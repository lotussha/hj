<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/8/10 10:32
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
 * 商品日志校验
 * Class GoodsLogValidate
 * @package app\common\validate
 */
class GoodsLogValidate extends Validate
{

    protected $rule = [
        'goods_id'        => 'require|number',
        'opt_source'      => 'require|checkSource',
        'operator'        => 'require|number',
        'do'              => 'require|checkDoType'
    ];

    protected $message = [
        'goods_id.require' => '商品ID不能为空',
        'goods_id.number' => '商品ID数据类型错误',
        'opt_source.require' => '操作来源不能为空',
        'operator.require' => '操作者ID不能为空',
        'operator.number' => '操作者ID数据类型错误',
        'do.require' => '操作类型不能为空',
    ];

    // 自定义操作来源校验
    protected function checkSource($value){
        if(!array_key_exists($value,config('log_params.goods_log.opt_source'))) {
            return "操作来源类型错误！";
        }
        return true;
    }

    // 自定义操作类型校验
    protected function checkDoType($value){
        if(!array_key_exists($value,config('log_params.goods_log.do_type'))) {
            return "操作类型错误！";
        }
        return true;
    }

}
