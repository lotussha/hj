<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/8/12 10:49
 *                ..::::::::::::.                                女神保佑，代码无bug！！！
 *              ``::::::::::::::::                               Codes are far away from bugs with the goddess！！！
 *               ::::``:::::::::'        .:::.
 *              ::::'   ':::::'       .::::::::.
 *            .::::'      ::::     .:::::::'::::.
 *           .:::'       :::::  .:::::::::' ':::::.
 *          .::'        :::::.:::::::::'      ':::::.
 *         .::'         ::::::::::::::'         ``::::.
 *     ...:::           ::::::::::::'              ``::.
 *    ````':.          ':::::::::'                  ::::..
 *                       '.:::::'                    ':'````..
 *
 */
namespace app\common\validate;

use think\Validate;

/**
 * 营销管理 - 菜谱
 * Class MarkeringMenuValidate
 * @package app\common\validate
 */
class MarkeringMenuValidate extends Validate
{

    protected $rule = [
        'id|id'                       => 'require|gt:0',
        'cate_id|分类ID'              => 'require|gt:0',
        'menu_title|菜谱名称'         => 'require',
        'menu_synopsis|菜谱简介'       => 'require',
        'food_ingredients|食材'        => 'require',
        'main_images|菜谱主图'         => 'require',
        'menu_videos|菜谱视频'         => 'require',
        'goods_id|商品ID'              => 'require|gt:0',
        'menu_details|菜谱步骤'        => 'require',
        'status|操作状态'              => 'require'
    ];

    protected $message = [
        'id.require' => 'id不能为空',
        'cate_id.require' => '菜谱分类不能为空',
        'menu_title.require' => '菜谱名称不能为空',
        'food_ingredients.require' => '食材不能为空',
        'main_images.require' => '菜谱主图不能为空',
        'menu_videos.require' => '菜谱视频不能为空',
        'goods_id.require' => '商品不能为空',
        'menu_details.require' => '菜谱步骤不能为空',
        'status.require'       => '操作状态不能为空'
    ];

    protected $scene = [
        'add' => ['cate_id','menu_title','menu_synopsis','food_ingredients','main_images','menu_videos','goods_id','menu_details'],
        'edit' => ['id','cate_id','menu_title','menu_synopsis','food_ingredients','main_images','menu_videos','goods_id','menu_details'],
        'delete' => ['id'],
        'state' => ['id','status'],
        'read'  => ['id']
    ];

}
