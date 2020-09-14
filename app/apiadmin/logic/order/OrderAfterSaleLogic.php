<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/17
 * Time: 19:32
 */

namespace app\apiadmin\logic\order;


use app\common\model\order\OrderAftersalesModel;
use app\common\validate\OrderAftersalesValidate;
use sakuno\utils\JsonUtils;
use think\facade\Db;

class OrderAfterSaleLogic
{
    protected $orderAftersalesModel;
    public function __construct(OrderAftersalesModel $orderAftersalesModel)
    {
        $this->orderAftersalesModel = $orderAftersalesModel;
    }

    /**
     * 获取列表
     * User: Jomlz
     * Date: 2020/8/17 21:29
     */
    public function getList($param)
    {
        $field = ['id,order_sn,order_id,rec_id,identity,identity_id,user_id,aftersales_type,goods_num,status,add_time'];
        $list_rows = $param['list_rows'] ?? 10;
        $lists = $this->orderAftersalesModel
            ->with(['orderInfo','orderGoods','adminUser','user'])
            ->field($field)
            ->scope('where', $param)
            ->append(['add_time_date','identity_text','aftersales_type_text','status_text'])
            ->hidden(['add_time','status','aftersales_type','order_id','rec_id','identity','identity_id'])
            ->paginate($list_rows)->toArray();
//        dump($lists);die;
        foreach ($lists['data'] as $key=>$val){
            $lists['data'][$key]['goods_name'] = $val['orderGoods']['goods_name'];
            $lists['data'][$key]['consignee'] = $val['orderInfo']['consignee'];
            $lists['data'][$key]['identity_nickname'] = $val['adminUser']['nickname'];
            $lists['data'][$key]['final_price'] = $val['orderGoods']['final_price'] ?? $val['orderGoods']['goods_price'];
            $lists['data'][$key]['pay_time_date'] = $val['orderInfo']['pay_time_date'];
            $lists['data'][$key]['nick_name'] = $val['user']['nick_name'] ?? $val['user']['username'];
            unset($lists['data'][$key]['adminUser'],$lists['data'][$key]['orderGoods'],$lists['data'][$key]['orderInfo'],$lists['data'][$key]['user']);
        }
        return $lists;
    }
    
    public function aftersalesInfo($param,$where = [])
    {
        $field = 'id,order_sn,order_id,rec_id,identity,identity_id,user_id,aftersales_type,goods_num,status,add_time';
        $append = ['add_time_date','aftersales_type_text','status_text','after_button','user_delivery_info','seller_delivery_info'];
        $data = $this->orderAftersalesModel
//            ->field($field)
            ->with(['orderInfo','orderGoods','adminUser','user'])
            ->scope('where', $param)
            ->where($where)
            ->append($append)
            ->find();
        return $data;
    }

    /**
     * 获取详情
     * User: Jomlz
     * Date: 2020/8/17 21:29
     */
    public function getDetail($param)
    {
        $validate = new OrderAftersalesValidate();
        $validate_result = $validate->scene('info')->check($param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $where = [['id','=',$param['id']]];
        $data = $this->aftersalesInfo($param,$where);
        if (empty($data)){
            return JsonUtils::fail('信息不存在');
        }
        $data = $data->toArray();
//        dump($data);die;
        //身份信息
        if (!isset($param['identity'])){
            $res['identity_info'] = [
                'identity' => $data['orderInfo']['identity_text'],
                'identity_id' => $data['orderInfo']['identity_id'],
                'identity_nickname' => $data['adminUser']['nickname'],
            ];
        }
        //基本信息
        $res['basic_info'] = [
            'id' => $data['id'],
            'order_id' => $data['order_id'],
            'order_sn' => $data['order_sn'],
            'nick_name' => $data['user']['nick_name'],
            'mobile' => '',
            'order_status' =>$data['orderInfo']['order_status_text'].'/'.$data['orderInfo']['pay_status_text'].'/'.$data['orderInfo']['shipping_status_text'],
            'add_time_data' =>$data['orderInfo']['add_time_data'],
            'pay_time_date' =>$data['orderInfo']['pay_time_date'],
            'pay_type_text' =>$data['orderInfo']['pay_type_text'],
            'aftersales_type_text' =>$data['aftersales_type_text'],
            'aftersales_type' =>$data['aftersales_type'],
            'status' =>$data['status'],
            'is_receive' =>$data['is_receive'],
            'status_text' =>$data['status_text'],
            'reason' =>$data['reason'],
            'description' =>$data['description'],
            'evidence_pic' =>$data['evidence_pic'],
            'refunds_reason' =>$data['refunds_reason'],
            'shop_explanation' =>$data['shop_explanation'],
        ];
        //收货信息
        $res['receiving_info'] = [
            'consignee' => $data['orderInfo']['consignee'],
            'receiver_tel' => $data['orderInfo']['receiver_tel'],
            'full_address' => $data['orderInfo']['full_address'],
            'zip' => $data['orderInfo']['zip'],
            'user_note' => $data['orderInfo']['user_note'],
        ];
        //退货退款，换货的用户发货信息
        $res['user_delivery_info'] = $data['user_delivery_info'];
        $res['seller_delivery_info'] = $data['seller_delivery_info'];
        //商品信息
        $res['order_goods'] = [
//            'rec_id' => $data['orderGoods']['rec_id'],
            'goods_id' => $data['orderGoods']['goods_id'],
            'goods_sn' => $data['orderGoods']['goods_sn'],
            'goods_name' => $data['orderGoods']['goods_name'],
            'spec_key_name' => $data['orderGoods']['spec_key_name'],
            'goods_price' => $data['orderGoods']['goods_price'],
            'final_price' => $data['orderGoods']['final_price'],
            'goods_num' => $data['goods_num'],
            'total_final_price' => $data['orderGoods']['final_price'] * $data['goods_num'],
        ];
        //费用信息
        $res['price_info'] = [
            'goods_price' =>$data['orderGoods']['final_price'] * $data['goods_num'],
            'shipping_price' =>$data['orderInfo']['shipping_price'],
            'integral_money' =>$data['orderInfo']['integral_money'],
            'coupon_price' =>$data['orderInfo']['coupon_price'],
            'user_money' =>$data['orderInfo']['user_money'],
            'readjust_price' =>$data['orderGoods']['readjust_price'],
            'actual_amount' =>$data['orderInfo']['actual_amount'],
        ];
        $res['handle'] = $data['after_button'];
        return JsonUtils::successful('获取成功',$res);
    }

    /**
     * 售后操作
     * User: Jomlz
     */
    public function handle($param)
    {
        $validate = new OrderAftersalesValidate();
        $validate_result = $validate->scene('handle')->check($param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $where = [
            ['id','=',$param['id']],
            ['status','in',[0,1,2]],
        ];
        $data = $this->aftersalesInfo($param,$where);
        if (empty($data)){
            return JsonUtils::fail('信息不存在');
        }
        $data = $data->toArray();
        //验证可执行操作
        $btn_status = [];
        foreach ($data['after_button'] as $k=>$v){
            array_push($btn_status,$v['btn_status']);
        }
        if (!in_array($param['btn_status'],$btn_status)){
            return JsonUtils::fail('不能此操作');
        }
        switch ($param['btn_status']){
            case 1: //申请通过
                if ($data['status'] != 0){
                    return JsonUtils::fail('参数错误');
                }
                $update['status'] = 1;
                $update['checktime'] = time();
                $update['shop_explanation'] = $param['shop_explanation'] ?? '';
                break;
            case 2: //申请不通过
                if ($data['status'] != 0){
                    return JsonUtils::fail('参数错误');
                }
                $update['status'] = -1;
                $update['checktime'] = time();
                $update['shop_explanation'] = $param['shop_explanation'] ?? '';
                break;
            case 3: //支付原路退回
                return JsonUtils::fail('此功能未开放');
                $update['refund_time'] = time();
                $update['refunds_reason'] = $param['refunds_reason'] ?? '';
                break;
            case 4: //退回到用户余额
                return JsonUtils::fail('此功能未开放');
                $update['refund_time'] = time();
                $update['refunds_reason'] = $param['refunds_reason'] ?? '';
                break;
            case 5: //重新发货
                $seller_delivery = json_decode($param['seller_delivery'],true);
                $seller_delivery['express_time'] = time();
                $update['seller_delivery'] = serialize($seller_delivery);
                $update['status'] = 3;
                break;
            case 6: //商家确认收货
                $update['is_receive'] = 1;
                $update['receivetime'] = time();
                break;
            default:
                return JsonUtils::fail('btn_status参数有误');
        }

        if (Db::name('order_aftersales')->where(['id'=>$param['id']])->save($update)){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败');
        }
    }
}