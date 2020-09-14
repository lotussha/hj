<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/17
 * Time: 11:49
 */

namespace app\common\validate;

use think\Validate;

class OrderValidate extends Validate
{
    protected $rule = [
        'id|订单ID' => 'require',
        'rec_id|订单商品rec_id' => 'require',
        'readjust_price|调整价格' => 'require|regex:/^(\-)?\d+(\.\d{1,2})?$/',
        'btn_status|操作参数btn_status' => 'require|number|gt:0',
    ];

    protected $message = [
        'readjust_price.regex' => '调整的价格最多小数点后两位'
    ];

    protected $scene = [
        'info' => ['id'],
        'readjust' => ['id','rec_id','readjust_price'],
        'handle' => ['id','btn_status'],
    ];
}