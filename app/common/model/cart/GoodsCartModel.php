<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/21
 * Time: 16:50
 */

namespace app\common\model\cart;

use app\common\model\CommonModel;
use app\common\model\GoodsModel;

class GoodsCartModel extends CommonModel
{
    protected $name = 'goods_cart';

    public function goods()
    {
        return $this->hasOne(GoodsModel::class, 'goods_id', 'goods_id')
            ->field('goods_id,is_on_sale,original_img,prom_type,prom_id,is_del,is_check,identity,identity_id');
    }

    /**
     * 检测活动商品是否能加入购物车
     * @param int $promType
     * @return bool
     */
    public function checkAddCart($promType)
    {
        if (in_array($promType, array_values([0,2]))) {
            return true;
        } else {
            return false;
        }
    }

}