<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 */

namespace app\common\logic\activity;

use app\common\model\ActivityModel;
use app\common\model\GoodsModel;
use app\common\model\GoodsSpecPriceModel;
use think\facade\Db;

/**
 * 预售逻辑定义
 * Class PreSaleLogic
 * @package app\common\logic\activity
 */

class PreSaleLogic extends Prom
{
    protected $preSale;//抢购活动模型
    protected $goods;//商品模型
    protected $specGoodsPrice;//商品规格模型

    public function __construct($goods,$specGoodsPrice)
    {
        $this->goods = $goods;
        $this->specGoodsPrice = $specGoodsPrice;
        if($this->specGoodsPrice){
            //活动商品有规格，规格和活动是一对一
            $this->preSale = ActivityModel::find($specGoodsPrice['prom_id']);
        }else{
            //活动商品没有规格，活动和商品是一对一
            $this->preSale = ActivityModel::find($goods['prom_id']);
        }
        if ($this->preSale) {
            //每次初始化都检测活动是否结束，如果失效就更新活动和商品恢复成普通商品
            if ($this->checkActivityIsEnd() && $this->preSale['is_end'] == 0) {
                if($this->specGoodsPrice){
                    //批量修改规格为活动结束
                    Db::name('goods_spec_price')->where([['goods_id','=',$this->specGoodsPrice['goods_id']],['prom_id','=',$specGoodsPrice['prom_id']]])->save(['is_end' => 1]);
                    //恢复普通商品
                    Db::name('goods')->where("goods_id", $this->specGoodsPrice['goods_id'])->save(['prom_type' => 0, 'prom_id' => 0]);
                    unset($this->specGoodsPrice);
                    //重新获取普通商品规格
                    $this->specGoodsPrice = (new GoodsSpecPriceModel())
                        ->where(['goods_id'=>$specGoodsPrice['goods_id'],'key'=>$specGoodsPrice['key'],'prom_id'=>0])
                        ->append(['spec_price'])
                        ->find();
                }else{
                    Db::name('goods')->where("goods_id", $this->preSale['goods_id'])->save(['prom_type' => 0, 'prom_id' => 0]);
                }
                $this->preSale->is_end = 1;
                $this->preSale->save();
                unset($this->goods);
                $this->goods = (new GoodsModel())->where(['goods_id'=>$goods['goods_id']])->find();
            }
        }
    }

    /**
     * 获取单个预售活动
     * @return static
     * User: Jomlz
     */
    public function getPromModel()
    {
        return $this->preSale;
    }

    /**
     * 获取商品原始数据
     * @return array|\think\Model|null
     * User: Jomlz
     */
    public function getGoodsInfo()
    {
        return $this->goods;
    }

    /**
     * 活动是否正在进行
     * @return bool
     * User: Jomlz
     */
    public function checkActivityIsAble()
    {
        if(empty($this->preSale)){
            return false;
        }
        if(time() > $this->preSale['start_time'] && time() < $this->preSale['end_time'] && $this->preSale['is_end'] == 0){
            return true;
        }
        return false;
    }

    /**
     * 活动是否结束
     * @return bool
     * User: Jomlz
     */
    public function checkActivityIsEnd()
    {

    }

    /**
     * 该活动是否已经失效
     * User: Jomlz
     */
    public function IsAble()
    {
        if(empty($this->preSale)){
            return false;
        }
        if($this->preSale['is_end'] == 1){
            return false;
        }
        if($this->preSale['buy_num'] >= $this->preSale['goods_num']){
            return false;
        }
        if(time() > $this->preSale['end_time']){
            return false;
        }
        return true;
    }

    /**
     * 获取商品转换活动商品的数据
     * User: Jomlz
     */
    public function getActivityGoodsInfo()
    {
        if($this->specGoodsPrice){
            //活动商品有规格，规格和活动是一对一
            $activityGoods = $this->specGoodsPrice;
        }else{
            //活动商品没有规格，活动和商品是一对一
            $activityGoods = $this->goods;
        }
        $activityGoods['activity_title'] = $this->preSale['title'];
        $activityGoods['price'] = $this->specGoodsPrice['spec_price']['price'];
        $activityGoods['deposit'] = $this->specGoodsPrice['spec_price']['deposit'];
        $activityGoods['store_count'] = $this->specGoodsPrice['spec_price']['store_count'];
        $activityGoods['start_time'] = $this->preSale['start_time'];
        $activityGoods['end_time'] = $this->preSale['end_time'];
        $activityGoods['buy_limit'] = $this->specGoodsPrice['limited_quantity'];
        $activityGoods['virtual_num'] =0;
        return $activityGoods;
    }

    /**
     * 活动是否可付尾款
     * User: Jomlz
     */
    public function checkActivityIsFinal()
    {
        if(empty($this->preSale)){
            return false;
        }
        if (time() > $this->preSale['final_payment_start_time'] && time() < $this->preSale['final_payment_end_time']){
            return true;
        }
        return false;
    }


}