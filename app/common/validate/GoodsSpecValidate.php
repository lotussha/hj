<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/4
 * Time: 14:45
 */

namespace app\common\validate;

use think\Validate;

class GoodsSpecValidate extends Validate
{
    protected $rule = [
        'id|id'      => 'require',
        'type_id|模型id'      => 'require',
        'name|规格名称'       => 'require',
        'order|排序'       => 'require',
    ];

    protected $message = [
    ];

    protected $scene = [
        'add'   => ['type_id','name'],
        'edit'  => ['id','type_id','name'],
        'del'  => ['id'],
        'info' => ['id'],
        'list' => ['type_id'],
    ];
}