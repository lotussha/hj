<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/22
 * Time: 14:56
 */

namespace app\api\validate;

use think\Validate;

class OrderValidate extends Validate
{
    protected $rule = [
        'id|订单ID' => 'require|number|gt:0',
        'rec_id|订单商品rec_id' => 'require',
        'goods_id|商品ID' => 'require|number|gt:0',
        'cart_id|购物车ID' => 'require',
        'key|商品规格' => 'require',
        'goods_num|商品数量' => 'require|number|gt:0',
        'address_id|收货地址' => 'require|number|gt:0',
    ];

    protected $message = [
    ];

    protected $scene = [
        'goods_confirm' => ['goods_id','key','goods_num'],
        'cart_confirm' => ['cart_id'],
        'goods_add_order' => ['goods_id','key','goods_num','address_id'],
        'cart_add_order' => ['cart_id','address_id'],
        'order_details' => ['id'],
        'order_goods' => ['id','rec_id'],
    ];
}