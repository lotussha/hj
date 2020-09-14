<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/26
 * Time: 11:06
 */

namespace app\common\validate\goods;

use think\Validate;

class GoodsServiceValidate extends Validate
{
    protected $rule = [
        'id|id'      => 'require|number|gt:0',
        'name|服务名称'      => 'require|unique:goods_service',
        'content|服务内容'       => 'require',
    ];

    protected $message = [
    ];

    protected $scene = [
        'info'   => ['id'],
        'add'    => ['name','content'],
        'edit'   => ['id','name','content'],
        'del'    => ['id'],
    ];
}