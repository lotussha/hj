<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/31
 * Time: 13:43
 */

namespace app\api\logic\order;


use app\common\model\order\OrderModel;

class OrderPreSaleLogic
{
    protected $orderModel;
    protected $afterSaleLogic;
    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->afterSaleLogic = new OrderAfterSaleLogic();
    }

    public function getList($param, $user_id)
    {
        $type = $param['type'] ?? 1;
        $field = 'id,parent_id,identity_id,order_sn,order_status,shipping_status,shipping_price,pay_status,comment_status,order_amount,add_time,prom_types,paid_money,goods_num';
        $order = 'add_time desc';
        switch ($type) {
            case 1: //全部
                $where = [];
                break;
            case 2: //支付尾款
                $where = [['pay_status','=',2]];
                break;
            case 3: //待发货
                $where = [['pay_status', '=', 1], ['shipping_status', '<>', 1]];
                break;
            case 4: //待收货
                $where = [['pay_status', '=', 1], ['shipping_status', '=', 1]];
                break;
            case 5: //已完成
                $where = [['pay_status', '=', 1], ['order_status', '=', 4]];
                break;
            case 6: //退款售后
                break;
        }
        if ($type == 6){
            $param_data['orderGoodsWhere'] = [['prom_type','=',3]];
            $res = $this->afterSaleLogic->getOrderAfterSale($param_data,$user_id);
            $lists = $res['lists'];
        }else{
            $lists = $this->orderModel
                ->with(['identity', 'orderGoods', 'orderGoods.GoodsInfo','payLog'])
                ->field($field)
                ->where($where)
                ->where([['prom_types','=',3],['parent_id','<>',0],['user_id', '=', $user_id],['is_del', '=', 0],['pay_status','<>',0]])
                ->append(['status_text', 'add_time_data'])
                ->hidden(['add_time','comment_status','shipping_status','order_status','identity_id'])
                ->order($order)
                ->select()->toArray();
        }
        $data['lists'] = $lists;
        return $data;
    }
}