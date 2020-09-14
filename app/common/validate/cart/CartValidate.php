<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/21
 * Time: 17:03
 */

namespace app\common\validate\cart;

use think\Validate;

class CartValidate extends Validate
{
    protected $rule = [
        'id|id'      => 'require',
        'goods_id|商品ID'      => 'require',
        'goods_num|商品数量'      => 'require',
        'key|规格ID'      => 'require',
        'selected|选中'      => 'require|number|between:0,1',
    ];

    protected $message = [
    ];

    protected $scene = [
        'add'   => ['goods_id','goods_num','spec_key'],
        'edit'  => ['goods_id','goods_num'],
        'del'  => ['id'],
        'change_num'  => ['id','goods_num'],
        'change_selected'  => ['id','selected'],
    ];
}