<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/1
 * Time: 16:23
 */

namespace app\apiadmin\validate;

use think\Validate;

class AdminRoleValidate extends Validate
{
    protected $rule = [
        'id|id'        => 'require|unique:admin_role',
        'name|名称'        => 'require|unique:admin_role',
        'description|介绍' => 'require',
        'url|权限'       => 'require',
    ];

    protected $scene = [
        'add'  => ['name', 'description','url'],
        'edit'  => ['id','name', 'description','url'],
        'info'  => ['id'],
        'del'  => ['id'],
    ];
}