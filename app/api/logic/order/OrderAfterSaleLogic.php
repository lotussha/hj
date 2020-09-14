<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/25
 * Time: 10:01
 */

namespace app\api\logic\order;


use app\common\model\order\OrderAftersalesModel;
use app\common\model\settlement\SettlementModel;
use sakuno\utils\JsonUtils;
use think\facade\Db;

class OrderAfterSaleLogic
{
    protected $aftersalesModel;
    public function __construct()
    {
        $this->aftersalesModel = new OrderAftersalesModel();
    }

    /**
     * 获取售后列表
     * User: Jomlz
     */
    public function getOrderAfterSale($param,$user_id)
    {
        $type = $param['type'] ?? 1;
        $field = 'id,order_id,order_sn,rec_id,goods_id,identity_id,aftersales_type,goods_num,spec_key,status';
        $order = 'add_time desc';
        $orderGoodsWhere = $param['orderGoodsWhere'] ?? [];
        switch ($type) {
            case 1: //全部
                $where = [];
                break;
            case 2: //待审核
                $where = [['status', '=', 0]];
                break;
            case 3: //审核中
                $where = [['status', '=', 0]];
                break;
            case 4: //审核成功
                $where = [['status', '=', 1]];
                break;
                break;
            case 5: //审核失败
                $where = [['status', '=', -1]];
                break;
        }
        $lists = $this->aftersalesModel->with(['identity','orderGoods'=>function($query) use ($orderGoodsWhere){
            $query->where($orderGoodsWhere);
        }])
            ->field($field)
            ->where($where)
            ->where([['user_id', '=', $user_id], ['is_del', '=', 0]])
            ->append(['status_reminder'])
            ->order($order)
            ->select()->toArray();
        foreach ($lists as $k=>$v){
            if (!$v['orderGoods']){
                unset($lists[$k]);
            }
        }
        $data['lists'] = array_values($lists);
       return $data;
    }

    /**
     * 添加售后
     * User: Jomlz
     */
    public function addAfterSale($param,$user_id)
    {
        $afterSale = Db::name('order_aftersales')->where(['order_id'=>$param['order_id'],'rec_id'=>$param['rec_id']])->find();
        if ($afterSale){
            return JsonUtils::fail('你已申请过了');
        }
        $order = Db::name('order')
            ->where(['id'=>$param['order_id'],'user_id'=>$user_id,'is_del'=>0,'pay_status'=>1])
            ->find();
        if (!$order){
            return JsonUtils::fail('订单错误');
        }
        $orderGoods = Db::name('order_goods')
            ->where(['order_id'=>$param['order_id'],'rec_id'=>$param['rec_id']])
            ->where([['is_deliver','<',2]])
            ->find();
        if (!$orderGoods){
            return JsonUtils::fail('订单商品错误');
        }
        if ($orderGoods['goods_num'] < $param['goods_num']){
            return JsonUtils::fail('售后商品数量错误');
        }
        if ($orderGoods['is_deliver'] == 0 && $param['aftersales_type'] != 1){
            return JsonUtils::fail('未发货只能退款');
        }
        $refund_money = $orderGoods['final_price']*$param['goods_num']; //要退的总价
        $data = [
            'order_sn'          => $order['order_sn'],
            'order_id'          => $param['order_id'],
            'rec_id'            => $param['rec_id'],
            'identity'          => $orderGoods['identity'],
            'identity_id'       => $orderGoods['identity_id'],
            'warehouse_id'      => $order['warehouse_id'],
            'user_id'           => $user_id,
            'aftersales_type'   => $param['aftersales_type'],
            'goods_id'          => $orderGoods['goods_id'],
            'goods_num'         => $param['goods_num'],
            'reason'            => $param['reason'] ?? '',
            'evidence_pic'      => $param['evidence_pic'] ?? '',
            'goods_name'        => $orderGoods['goods_name'],
            'spec_key'          => $orderGoods['spec_key'],
            'spec_key_name'     => $orderGoods['spec_key_name'],
            'add_time'          => time(),
            'refund_money'      => $refund_money,
        ];
        $result =  Db::name('order_aftersales')->insert($data);
        if ($result){
            return JsonUtils::successful('申请成功');
        }else{
            return JsonUtils::fail('申请失败');
        }
    }

    /**
     * 获取详情
     * User: Jomlz
     */
    public function getDetails($param,$user_id)
    {
        $fild = 'id,order_id,rec_id,identity_id,user_id,goods_id,aftersales_type,goods_num,reason,add_time,refund_money,status';
        $info = $this->aftersalesModel
            ->field($fild)
            ->where(['id'=>$param['id'],'user_id'=>$user_id,'is_del'=>0])
            ->append(['aftersales_type_text','status_reminder','add_time_date'])
            ->find();
        if (!$info){
            return JsonUtils::fail('信息错误');
        }
        $info = $info->toArray();
        //需要退货，查看商家收货地址
        $identity_address = [];
        if ($info['status_reminder']['sr_status'] == 3)
        {
            $identityInfo = (new SettlementModel())
                ->where(['admin_id'=>$info['identity_id']])
                ->append(['full_address'])
                ->find();
            $identity_address = [
                'contacts' => $identityInfo['contacts'],
                'phone' => $identityInfo['phone'],
                'full_address' => $identityInfo['full_address'],
            ];
        }
        $data = ['identity_address'=>$identity_address,'info'=>$info];
       return JsonUtils::successful('获取成功',$data);
    }

    /**
     * 用户发货
     * User: Jomlz
     */
    public function userDelivery($param,$user_id)
    {
        $fild = 'id,order_id,rec_id,identity_id,user_id,goods_id,aftersales_type,goods_num,reason,add_time,refund_money,status';
        $info = $this->aftersalesModel
            ->field($fild)
            ->where(['id'=>$param['id'],'user_id'=>$user_id,'is_del'=>0,'status'=>1])
            ->find();
        if (!$info || $info['aftersales_type'] < 2){
            return JsonUtils::fail('参数错误');
        }
        $delivery = [
            'express_name' => $param['express_name'],
            'express_sn' => $param['express_sn'],
            'express_time' => time(),
        ];
        $data['user_delivery'] = serialize($delivery);
        $data['status'] = 2;
        $info = $this->aftersalesModel->where(['id'=>$param['id']])->save($data);
        if ($info){
            return JsonUtils::successful('提交成功');
        }
        return JsonUtils::fail('提交失败');
    }

    /**
     * 取消售后
     * User: Jomlz
     */
    public function cancel($param,$user_id)
    {
        $info = $this->aftersalesModel->where(['id'=>$param['id'],'user_id'=>$user_id,'is_del'=>0])->find();
        if (!$info){
            return JsonUtils::fail('参数错误');
        }
        $res = $this->aftersalesModel->where(['id'=>$info['id']])->save(['status'=>-2,'canceltime'=>time()]);
        if ($res){
            return JsonUtils::successful('取消成功');
        }else{
            return JsonUtils::fail('取消失败');
        }
    }
}