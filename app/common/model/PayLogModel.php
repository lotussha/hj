<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * 支付记录
 */

namespace app\common\model;


class PayLogModel extends CommonModel
{
    protected $name = 'pay_log';

    /**
     * 插入支付记录
     * @return int|string
     * User: Jomlz
     */
    public function insertPayLog($order_id,$order_sn,$pay_amount,$pay_type,$user_id,$parent_id=0,$is_pay=0,$pay_body='')
    {
        $pay_log = array(
            'order_id' => $order_id,
            'order_sn' => $order_sn,
            'parent_id' => $parent_id,
            'pay_amount' => $pay_amount,
            'pay_type' => $pay_type,
            'user_id' => $user_id,
            'is_pay' => $is_pay,
            'pay_body' => $pay_body,
            'add_time' => time(),
        );
        $log_id = $this->insertGetId($pay_log);
        return $log_id;
    }
}