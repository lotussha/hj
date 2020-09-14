<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/1
 * Time: 14:25
 */

namespace app\apiadmin\validate;

use think\Validate;

class AdminMenuValidate extends Validate
{
    protected $rule = [
        'id|菜单id'      => 'require',
        'parent_id|上级菜单' => ['require','egt:0'],
        'name|菜单名称'       => 'require',
        'url|菜单url'       => 'require',
        'icon|图标'      => 'require',
        'is_show|手机号'   => 'require',
        'sort_id|排序'   => 'require',
    ];

    protected $message = [
    ];

    protected $scene = [
        'add'   => ['name','url'],
        'edit'  => ['id','name','url'],
        'del'  => ['id'],
        'info' => ['id'],
    ];

}