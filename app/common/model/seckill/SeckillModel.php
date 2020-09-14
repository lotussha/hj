<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/8/12 15:59
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
namespace app\common\model\seckill;

use app\common\model\CommonModel;

/**
 * 营销管理 - 秒杀model
 * Class SeckillModel
 * @package app\common\model\seckill
 */
class SeckillModel extends CommonModel
{
    protected $name = 'seckill';

    /**
     * 一对一关联分类模型 规格
     * @return \think\model\relation\HasOne
     */
    public function SpecInfo() {
        return $this->hasOne('app\common\model\GoodsSpecPriceModel', 'item_id', 'spec_id')->field('item_id,goods_id,key,key_name');
    }

    /**
     * 一对一关联分类模型 商品
     * @return \think\model\relation\HasOne
     */
    public function GoodsInfo() {
        return $this->hasOne('app\common\model\GoodsModel', 'goods_id', 'goods_id')->field('goods_id,goods_name');
    }

    /**
     * 查看秒杀活动详情
     * @param array $where
     * @param string $field
     * @param array $hidden
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSeckillDetail($where = [],$field = '*',$hidden = []){
        $res = $this->where($where)
            ->field($field)
            ->hidden($hidden)
            ->with(['goods_info','spec_info'])
            ->find();
        if(!empty($res)){
            $res = $res->toArray();
        }
        return $res;
    }

}
