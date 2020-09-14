<?php
/**
 * Created by PhpStorm.
 * PHP version 版本号
 *
 * @category 类别名称
 * @package  暂无
 * @author   hj <138610033@qq.com>
 * @license  暂无
 * @link     暂无
 * DateTime: 2020/8/19 上午11:52
 */


namespace app\common\validate;

use think\Validate;

class ActivityValidate extends Validate
{
    protected $rule = [
        'id|活动ID' => 'require',
        'type|活动类型ID' => 'require|integer',
        'title|活动标题' => 'require',
        'goods_common|商品信息' => 'require',
    ];

    protected $message = [

    ];

    protected $scene = [
        'add' => ['type', 'title', 'goods_common'],
        'edit' => ['id', 'type', 'title', 'goods_common'],
        'del' => ['id'],
        'info' => ['id'],
    ];
}