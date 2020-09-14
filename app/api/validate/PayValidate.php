<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/24
 * Time: 14:57
 */

namespace app\api\validate;

use think\Validate;

class PayValidate extends Validate
{
    protected $rule = [
        'log_id|支付ID' => 'require|number|gt:0',
        'order_sn|订单号' => 'require',
        'pay_mode|支付方式' => 'require|number|gt:0',
    ];

    protected $message = [
    ];

    protected $scene = [
    ];
}