<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 */

namespace app\common\logic\goods;

use app\common\logic\activity\PreSaleLogic;
use app\common\logic\activity\SeckillLogic;

/**
 * 商品活动工厂类
 * Class GoodsPromFactory
 * @package app\common\logic\goods
 */
class GoodsPromFactory
{
    /**
     * @param $goods|商品实例
     * @param $spec_goods_price|规格实例
     * @return SeckillLogic|GroupBuyLogic|PromGoodsLogic
     */
    public function makeModule($goods, $spec_goods_price)
    {
        switch ($goods['prom_type']) {
            case 1:
//                return new GroupBuyLogic($goods, $spec_goods_price);
            case 2: //秒杀
                return new SeckillLogic($goods, $spec_goods_price);
            case 3:
                return  new PreSaleLogic($goods,$spec_goods_price);
            case 4:
//            return new PromGoodsLogic($goods, $spec_goods_price);
            case 6:
//                return new TeamActivityLogic($goods, $spec_goods_price);
        }
    }

    /**
     * 检测是否符合商品活动工厂类的使用
     * @param $promType |活动类型
     * @return bool
     * User: Jomlz
     */
    public function checkPromType($promType)
    {
//        if (in_array($promType, array_values([1, 2, 3, 4 , 5]))) {
        if (in_array($promType, array_values([2,3]))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 活动类型
     * @param $promType
     * @return string
     * User: Jomlz
     */
    public function promTypeText($promType)
    {
        $type = config('status')['PROM_TYPE'][$promType] ?? '未知';
        return $type;
    }
}