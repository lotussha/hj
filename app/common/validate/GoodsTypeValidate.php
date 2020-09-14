<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/4
 * Time: 11:04
 */

namespace app\common\validate;

use think\Validate;

class GoodsTypeValidate extends Validate
{
    protected $rule = [
        'id|id'      => 'require',
        'name|名称'       => 'require',
    ];

    protected $message = [
    ];

    protected $scene = [
        'add'   => ['name'],
        'edit'  => ['id','name'],
        'del'  => ['id'],
        'info' => ['id'],
    ];
}