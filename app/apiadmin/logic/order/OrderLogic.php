<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/14
 * Time: 14:24
 */

namespace app\apiadmin\logic\order;

use app\apiadmin\model\AdminUsers;
use app\common\model\order\OrderGoodsModel;
use app\common\model\order\OrderModel;
use app\common\validate\OrderDeleverValidate;
use app\common\validate\OrderValidate;
use sakuno\utils\JsonUtils;
use think\facade\Db;

class OrderLogic
{
    protected $orderModel;
    public function __construct(OrderModel $orderModel)
    {
        $this->orderModel = $orderModel;
    }

    /**
     * 获取订单列表
     * User: Jomlz
     */
    public function getList($param)
    {
        $field = ['id,parent_id,user_id,identity,identity_id,order_sn,pay_type,consignee,receiver_tel,goods_price,shipping_type,goods_num,order_amount,add_time,order_status,shipping_status,pay_status,pay_time'];
        $where = $param['where'] ?? [['parent_id', '<>', 0]];
        $list_rows = $param['list_rows'] ?? 10;
        $lists = $this->orderModel
            ->with(['adminUser', 'user'])
            ->where($where)
            ->field($field)
            ->scope('where', $param)
            ->append(['add_time_data', 'pay_time_date', 'identity_text', 'pay_type_text', 'shipping_type_text', 'order_status_text', 'shipping_status_text', 'pay_status_text'])
            ->hidden(['pay_status', 'shipping_status', 'order_status', 'add_time', 'shipping_type', 'pay_type', 'identity_id', 'identity', 'pay_time'])
            ->paginate($list_rows)->toArray();
//        dump( $lists);die;
        foreach ($lists['data'] as $key => $val) {
            $lists['data'][$key]['identity_nickname'] = $val['adminUser']['nickname'] ?? '';
            $lists['data'][$key]['user_nickname'] = $val['user_id'] . '-' . $val['user']['nick_name'] ?? '';
            unset($lists['data'][$key]['adminUser'], $lists['data'][$key]['user']);
        }
        return $lists;
    }

    /**
     * 获取订单详情
     * User: Jomlz
     */
    public function getOrderDetail($param, $where = [])
    {
        $validate = new OrderValidate;
        $validate_result = $validate->scene('info')->check($param);
        if (!$validate_result) {
            return ['status' => 0, 'msg' => $validate->getError()];
        }
        $field = ['id,parent_id,identity,identity_id,user_id,order_sn,pay_type,consignee,goods_price,shipping_type,order_amount,
        add_time,pay_time,order_status,shipping_status,pay_status,prom_types'];
        $order_info = $this->orderModel->field('id,parent_id')->where([['id', '=', $param['id']], ['parent_id', '<>', 0]])->where($where)->scope('where', $param)->find();
        if (empty($order_info)) {
            return ['status' => 0, 'msg' => '订单不存在'];
        }
        $order_info = $order_info->toArray();
        $append = ['add_time_data', 'pay_time_date', 'identity_text', 'pay_type_text', 'shipping_type_text', 'order_status_text', 'shipping_status_text', 'pay_status_text', 'order_button', 'fulla_ddress'];
        $data = [];
        //主订单,只展示不做操作，去掉
        if ($order_info['parent_id'] == 0) {
            $order = $this->orderModel->with(['orderSon', 'orderSon.orderGoods', 'user'])
//               ->field($field)
                ->where(['id' => $param['id']])
                ->append($append)
                ->hidden(['add_time', 'shipping_type', 'pay_type', 'identity', 'parent_id', 'shipping_status', 'pay_status', 'prom_types'])
                ->find()->toArray();
        } else {
            //子订单
            $order = $this->orderModel
//                ->field($field)
                ->with(['orderGoods', 'adminUser', 'user'])->where(['id' => $param['id']])
                ->append($append)
//                ->hidden(['add_time','shipping_type','pay_type','identity','parent_id','order_status','pay_status','prom_types'])
                ->find()->toArray();
        }
        //整理输出格式
        $data['base_info'] = [
            'id' => $order['id'],
            'order_sn' => $order['order_sn'],
            'user_id' => $order['user_id'],
            'nick_name' => $order['user']['nick_name'] ? $order['user']['nick_name'] : $order['user']['username'],
            'user_tel' => $order['user']['username'] ?? '',
            'actual_amount' => $order['actual_amount'],
            'order_status_text' => $order['order_status_text'] . '/' . $order['pay_status_text'] . '/' . $order['shipping_status_text'],
            'add_time' => $order['add_time_data'],
            'pay_time' => $order['pay_time_date'],
            'pay_type' => $order['pay_type_text'],
            'shipping_status' => $order['shipping_status'],
            'order_status' => $order['order_status'],
            'pay_status' => $order['pay_status'],
        ];
        $data['addr_info'] = [
            'consignee' => $order['consignee'],
            'shipping_price' => $order['shipping_price'],
            'receiver_tel' => $order['receiver_tel'],
            'fulla_ddress' => $order['fulla_ddress'],
            'shipping_type_text' => $order['shipping_type_text'],
            'user_note' => $order['user_note'],
        ];
        $data['identity_arr'] = [
            'identity_info' => [
                'identity_id' => $order['identity_id'],
                'identity_text' => $order['identity_text'],
                'identity_nickname' => $order['adminUser']['nickname'],
            ],
            'goods_lists' => $order['orderGoods'],
        ];
        $data['cost_info'] = [
            'goods_price' => $order['goods_price'],
            'shipping_price' => $order['shipping_price'],
            'integral_money' => $order['integral_money'],
            'coupon_price' => $order['coupon_price'],
            'readjust_price' => $order['readjust_price'],
            'actual_amount' => $order['actual_amount'],
        ];
        $data['order_button'] = $order['order_button'];
        return ['status' => 1, 'msg' => '获取成功', 'data' => $data];
    }

    /**
     * 调整订单商品价格
     * User: Jomlz
     * Date: 2020/8/18 11:34
     */
    public function readjustOrderGoodsPrice($param)
    {
        $validate = new OrderValidate;
        $validate_result = $validate->scene('readjust')->check($param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $where = [['id', '=', $param['id']], ['parent_id', '>', 0], ['pay_status', '=', 0]];
        $field = 'id,parent_id,identity,identity_id,pay_status,readjust_price,actual_amount';
        $order = $this->orderModel->with(['orderGoods' => function ($query) use ($param) {
            $query->field('rec_id,order_id,goods_id,goods_name,goods_price,final_price,goods_num')->where(['order_id' => $param['id'], 'rec_id' => $param['rec_id']]);
        }])->scope('where', $param)->where($where)->field($field)->find();
        if (empty($order) || empty($order['orderGoods'])) {
            return JsonUtils::fail('订单信息错误');
        }
        $total_readjust_price = $param['readjust_price'] * $order['orderGoods'][0]['goods_num']; //调整总价
        // 启动事务
        Db::startTrans();
        try {
            //订单商品表调整价格
            Db::name('order_goods')->where(['order_id' => $param['id'], 'rec_id' => $param['rec_id']])
                ->inc('readjust_price', $param['readjust_price'])
                ->inc('final_price', $param['readjust_price'])
                ->update();
            //订单表子订单调整
            Db::name('order')->where(['id' => $param['id']])
                ->inc('readjust_price', $total_readjust_price)
                ->inc('actual_amount', $total_readjust_price)
                ->update();
            //订单表父订单调整
            Db::name('order')->where(['id' => $order['parent_id']])
                ->inc('readjust_price', $total_readjust_price)
                ->inc('actual_amount', $total_readjust_price)
                ->update();
            //支付记录调整支付金额
            Db::name('pay_log')->where(['order_id' =>  $param['id']])
                ->inc('pay_amount', $total_readjust_price)
                ->update();
            Db::commit();
            return JsonUtils::successful('调整成功');
        } catch (\Exception $e) {
            Db::rollback();
            return JsonUtils::fail($e->getMessage());
        }
    }

    /**
     * 订单操作
     * User: Jomlz
     */
    public function orderProcessHandle($param = [])
    {
        $validate = new OrderValidate;
        $validate_result = $validate->scene('handle')->check($param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $field = 'id,parent_id,order_status,pay_status,shipping_status,prom_types';
        $where = [
            ['id', '=', $param['id']],
            ['parent_id', '<>', 0],
        ];
        $orderInfo = $this->orderModel->field($field)->scope('where', $param)->where($where)->append(['order_button'])->find();
        if (!$orderInfo) {
            return JsonUtils::fail('订单不存在');
        }
        $btn_status = $param['btn_status'];
        //检验可执行操作
        $arr = [];
        foreach ($orderInfo['order_button'] as $k => $v) {
            $arr[] = $v['btn_status'];
        }
        if (!in_array($btn_status, $arr)) {
            return JsonUtils::fail('该订单不能执行此操作');
        }
        switch ($btn_status) {
            case 1: //确认订单
                $update['order_status'] = 1;
                break;
            case 3: //取消订单
                if ($orderInfo['order_status'] != 1) {
                    return JsonUtils::fail('该订单不能执行【取消订单】操作');
                }
                $update['order_status'] = 3;
                break;
            case 4: //付款
                return JsonUtils::fail('此功能未开放');
                //调用付款流程
                break;
            case 5: //设为未付款
                return JsonUtils::fail('此功能未开放');
                break;
            case 7: //作废
                return JsonUtils::fail('此功能未开放');
                break;
            case 8: //确认收货
                return JsonUtils::fail('此功能未开放');
                //调用收货流程
                break;
            default:
                return JsonUtils::fail('btn_status参数有误');
        }
        //记录操作订单日志
        //改变订单状态
        $update['updated_time'] = time();
        $update['admin_note'] = $param['admin_note'] ?? '';
        if (Db::name('order')->where(['id' => $param['id']])->save($update)) {
            return JsonUtils::successful('操作成功');
        } else {
            return JsonUtils::fail('操作失败');
        }
    }

    /**
     * 获取发货单详情
     * User: Jomlz
     */
    public function getDeliveryDetail($param)
    {
        $where = [['order_status', 'in', [1, 2, 4]], ['pay_status', '=', 1], ['shipping_type', '=', 1]];
        $res = $this->getOrderDetail($param, $where);
        if ($res['status'] == 0) {
            return ['status' => 0, 'msg' => $res['msg']];
        }
        $goods_lists = $res['data']['identity_arr']['goods_lists'];
        unset($res['data']['cost_info'], $res['data']['order_button']);
        if ($res['data']['base_info']['shipping_status'] != 1) {
            $btn_arr[] = ['btn_status' => 1, 'btn_name' => '确定发货'];
        } else {
            $btn_arr[] = [];
//            $btn_arr[] = ['btn_status' => 2, 'btn_name' => '修改'];
        }
        //订单商品加入发货单信息
        foreach ($goods_lists as $k=>$v){
            if ($v['is_deliver'] > 0){
                $delivery_info = Db::name('order_delivery')->field('shipping_name,invoice_no')->where(['id'=>$v['delivery_id']])->find();
            }
            $goods_lists[$k]['delivery_info'] = $delivery_info ?? (object)[];
        }
        $res['data']['identity_arr']['goods_lists'] = $goods_lists;
        $res['data']['order_button'] = $btn_arr;
        return ['status' => 1, 'msg' => '获取成功', 'data' => $res['data']];
    }

    /**
     * 发货操作
     * User: Jomlz
     */
    public function deleverHandle($param)
    {
        $validate = new OrderDeleverValidate();
        $validate_result = $validate->scene('delever_handle')->check($param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $res = $this->getDeliveryDetail($param);
        if ($res['status'] == 0) {
            return ['status' => $res['status'], 'msg' => $res['msg']];
        }
        //去发货

        $orderInfo = $this->orderModel->where(['id' => $param['id']])->find()->toArray();
        $orderGoods = $res['data']['identity_arr']['goods_lists'];
        $rec_id_arr = explode(',', $param['rec_ids']);
        $rec_ids = []; //未发货商品
        $aft_ids = []; //售后商品
        foreach ($orderGoods as $k => $v) {
            if ($v['is_deliver'] == 0) {
                $rec_ids[$k] = $v['rec_id'];
            }
            if ($v['is_deliver'] > 1){
                $aft_ids[$k] = $v['rec_id'];
            }
        }
        foreach ($rec_id_arr as $k => $v) {
            if (in_array($v, $aft_ids)){
                return ['status' => 0, 'msg' => '此订单商品已进入售后，不能发货'];
            }
            if (!in_array($v, $rec_ids)) {
                return ['status' => 0, 'msg' => '订单商品'.$v .'已发货'];
            }
        }
//        dump($rec_ids);die;
        $deliverData = [
            'order_id' => $orderInfo['id'],
            'rec_ids' => $param['rec_ids'],
            'order_sn' => $orderInfo['order_sn'],
            'user_id' => $orderInfo['user_id'],
            'consignee' => $orderInfo['consignee'],
            'zipcode' => $orderInfo['zip'] ?? '',
            'mobile' => $orderInfo['receiver_tel'],
            'province' => $orderInfo['province'],
            'city' => $orderInfo['city'],
            'county' => $orderInfo['county'],
            'twon' => $orderInfo['twon'],
            'address' => $orderInfo['address'],
            'invoice_no' => $param['invoice_no'],
            'shipping_name' => $param['shipping_name'],
            'shipping_price' => $orderInfo['shipping_price'],
            'best_time' => '',
            'send_type' => '',
            'identity' => $orderInfo['identity'],
            'identity_id' => $orderInfo['identity_id'],
            'warehouse_id' => $orderInfo['warehouse_id'],
            'note' => $param['note'],
            'add_time' => time(),
        ];
//        dump($deliverData);die;
        // 启动事务
        Db::startTrans();
        try {
            $delivery_id = Db::name('order_delivery')->insertGetId($deliverData);
            $delivery_num = 0;
            foreach ($orderGoods as $k => $v) {
                if ($v['is_deliver'] >= 1) {
                    $delivery_num++;
                }
                if ($v['is_deliver'] == 0 && in_array($v['rec_id'], $rec_id_arr)) {
                    $up_og['is_deliver'] = 1;
                    $up_og['delivery_id'] = $delivery_id;
                    Db::name('order_goods')->where("rec_id=" . $v['rec_id'])->save($up_og);//改变订单商品发货状态
                    $delivery_num++;
                }
            }
            $update['last_deliver_time'] = time();
            if ($delivery_num == count($orderGoods)) {
                $update['shipping_status'] = 1;
            } else {
                $update['shipping_status'] = 2;
            }
            Db::name('order')->where("id=" . $param['id'])->update($update);//改变订单状态
            //查询主订单下的全部商品状态
            $all_order_goods = Db::name('order_goods')->where(['order_parent_id' => $orderInfo['parent_id']])->select()->toArray();
            $all_deliver_num = 0;
            foreach ($all_order_goods as $k => $v) {
                if ($v['is_deliver'] == 1) {
                    $all_deliver_num++;
                }
            }
            if ($all_deliver_num == count($all_order_goods)) {
                $up['shipping_status'] = 1;
            } else {
                $up['shipping_status'] = 2;
            }
            Db::name('order')->where(['id' => $orderInfo['parent_id']])->update($up);//改变订单状态
            // 提交事务
            Db::commit();
            return ['status' => 1, 'msg' => '发货成功'];
        } catch (\Exception $e) {
            Db::rollback();
            return ['status' => 0, 'msg' => $e->getMessage()];
        };
    }

}