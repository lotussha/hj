<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/19
 * Time: 10:29
 */

namespace app\common\validate;

use think\Validate;

class OrderDeleverValidate extends Validate
{
    protected $rule = [
        'id|订单ID' => 'require',
        'rec_ids|物流单号' => 'require',
        'invoice_no|订单商品rec_id' => 'require',
        'shipping_name|快递名称' => 'require',
        'note|发货备注' => 'require',
    ];

    protected $message = [
        'readjust_price.regex' => '调整的价格最多小数点后两位'
    ];

    protected $scene = [
        'delever_handle' => ['id','rec_ids','invoice_no','shipping_name'],
    ];
}