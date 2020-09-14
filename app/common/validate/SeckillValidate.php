<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/8/12 15:34
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
 * 秒杀
 * Class SeckillValidate
 * @package app\common\validate
 */
class SeckillValidate extends Validate
{
    protected $rule = [
        'id|id'                                 => 'require|integer|gt:0',
        'time_id|秒杀时间段ID'                  => 'require|integer|gt:0',
        'goods_id|商品ID'                       => 'require|integer|gt:0',
        'spec_id|商品规格id'                    => 'require|integer|gt:0',
        'seckill_name|秒杀标题'                 => 'require|max:255',
        'seckill_desc|秒杀简介'                 => 'require|max:255',
        'seckill_image|秒杀封面图'              => 'require',
        'seckill_images|秒杀图片组'             => 'require',
        'seckill_price|秒杀价格'                => 'require|gt:0',
        'seckill_stock|秒杀库存'                => 'require|gt:0',
        'seckill_quota|秒杀单笔限购数量'         => 'require|number',
        'seckill_quota_total|限购总数量'         => 'require|number',
        'start_time|开始时间'                   => 'require|date',
        'stop_time|结束时间'                    => 'require|date',
        'is_show|推荐'                          => 'require|in:0,1',
        'status|状态'                           => 'require|integer|in:1,2',
    ];

    protected $message = [

    ];

    protected $scene = [
        'add' => ['time_id','goods_id','spec_id','seckill_name','seckill_name','seckill_desc','seckill_image','seckill_images','seckill_price','seckill_stock',
            'seckill_quota','seckill_quota_total','start_time','stop_time','is_show'],
        'edit' => ['id','time_id','goods_id','spec_id','seckill_name','seckill_name','seckill_desc','seckill_image','seckill_images','seckill_price','seckill_stock',
            'seckill_quota','seckill_quota_total','start_time','stop_time','is_show'],
        'delete' => ['id'],
        'state' => ['id','status'],
        'read'  => ['id']
    ];


}
