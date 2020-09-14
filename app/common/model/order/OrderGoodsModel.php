<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/14
 * Time: 21:50
 */

namespace app\common\model\order;

use app\common\model\CommonModel;
use app\common\model\GoodsModel;
use app\common\model\GoodsSpecPriceModel;

class OrderGoodsModel extends CommonModel
{
    protected $name='order_goods';

    public function GoodsInfo()
    {
        return $this->hasOne(GoodsModel::class,'goods_id','goods_id')->field('goods_id,original_img');
    }

    public function aftersalesGoods()
    {
        return $this->hasOne(OrderAftersalesModel::class,'rec_id','rec_id')->field('rec_id,status,aftersales_type');
    }

    //关联发货单
    public function delivery()
    {
        return $this->hasOne(OrderDelivery::class,'id','delivery_id');
    }

    //关联商品规格
    public function itemInfo()
    {
        return $this->hasOne(GoodsSpecPriceModel::class,'item_id','item_id');
    }

    //订单商品小计
    public function getSubtotalPriceAttr($val,$data)
    {
        return $data['final_price'] * $data['goods_num'];
    }
}