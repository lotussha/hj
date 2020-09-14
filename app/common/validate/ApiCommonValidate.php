<?php


namespace app\common\validate;


use think\Validate;

class ApiCommonValidate extends Validate
{
    protected $rule = [
//        'log_id|ID' => 'require',
//        'order_sn|订单号' => 'require',
    ];

    protected $message = [

    ];

    protected $scene = [
//        'wxxpay' => ['log_id','order_sn'],  //微信小程序支付


    ];
}