<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/4
 * Time: 17:07
 */

namespace app\common\validate;

use think\Validate;

class GoodsAttributeValidate extends Validate
{
    protected $rule = [
        'type_id|模型id'                    => 'require',
        'attr_id|属性id'                      => 'require',
        'attr_name|属性名称'                         => 'require',
        'attr_input_type|属性值的录入方式'       => 'require',
        'attr_values|可选值'                    => 'require',
    ];

    protected $message = [
    ];

    protected $scene = [
//        'add'   => ['type_id','attr_name','attr_input_type',''],
//        'edit'  => ['attr_id','type_id','attr_name','attr_input_type',''],
        'add'   => ['type_id','attr_name'],
        'edit'  => ['attr_id','type_id','attr_name'],
        'del'  => ['attr_id'],
        'info' => ['attr_id'],
        'list' => ['type_id'],
    ];
}