<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 */

namespace app\common\logic\activity;

use app\common\model\ActivityModel;
use app\common\model\GoodsModel;
use app\common\model\GoodsSpecPriceModel;
use app\common\model\seckill\SeckillModel;
use think\Exception;
use think\facade\Db;

/**
 * 秒杀逻辑定义
 * Class FlashSaleLogic
 * @package app\common\logic\activity
 */
class SeckillLogic extends Prom
{
    protected $flashSale;//抢购活动模型
    protected $goods;//商品模型
    protected $specGoodsPrice;//商品规格模型

    public function __construct($goods,$specGoodsPrice)
    {
        $this->goods = $goods;
        $this->specGoodsPrice = $specGoodsPrice;
        if($this->specGoodsPrice){
            //活动商品有规格，规格和活动是一对一
//            $this->flashSale = SeckillModel::find($specGoodsPrice['prom_id']);
            $this->flashSale = ActivityModel::find($specGoodsPrice['prom_id']);
        }else{
            //活动商品没有规格，活动和商品是一对一
//            $this->flashSale = SeckillModel::find($goods['prom_id']);
            $this->flashSale = ActivityModel::find($goods['prom_id']);
        }
        if ($this->flashSale) {
            //每次初始化都检测活动是否结束，如果失效就更新活动和商品恢复成普通商品
            if ($this->checkActivityIsEnd() && $this->flashSale['is_end'] == 0) {
                if($this->specGoodsPrice){
                    //批量修改规格为活动结束
                    Db::name('goods_spec_price')->where([['goods_id','=',$this->specGoodsPrice['goods_id']],['prom_id','=',$specGoodsPrice['prom_id']]])->save(['is_end' => 1]);
                    //恢复普通商品
                    Db::name('goods')->where("goods_id", $this->specGoodsPrice['goods_id'])->save(['prom_type' => 0, 'prom_id' => 0]);
                    unset($this->specGoodsPrice);
                    //重新获取普通商品规格
                    $this->specGoodsPrice = (new GoodsSpecPriceModel)
                        ->where(['goods_id'=>$specGoodsPrice['goods_id'],'key'=>$specGoodsPrice['key'],'prom_id'=>0])
                        ->append(['spec_price'])
                        ->find();
                }else{
                    Db::name('goods')->where("goods_id", $this->flashSale['goods_id'])->save(['prom_type' => 0, 'prom_id' => 0]);
                }
                $this->flashSale->is_end = 1;
                $this->flashSale->save();
                unset($this->goods);
                $this->goods = (new GoodsModel)->where(['goods_id'=>$goods['goods_id']])->find();
            }
        }
    }

    /**
     * 活动是否正在进行
     * @return bool
     * User: Jomlz
     */
    public function checkActivityIsAble()
    {
        if(empty($this->flashSale)){
            return false;
        }
        if(time() > $this->flashSale['start_time'] && time() < $this->flashSale['end_time'] && $this->flashSale['is_end'] == 0){
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
        if(empty($this->flashSale)){
            return true;
        }
        if($this->flashSale['buy_num'] >= $this->flashSale['goods_num']){
            return true;
        }
        if(time() > $this->flashSale['end_time']){
            return true;
        }
        return false;
    }

    /**
     * 获取用户抢购已购商品数量
     * @param $user_id
     * @return float|int
     * User: Jomlz
     */
    public function getUserFlashOrderGoodsNum($user_id){
        $orderWhere = [
            ['user_id','=',$user_id],
            ['order_status','<>',3],  //不包含已取消
            ['add_time','between',[$this->flashSale['start_time'],$this->flashSale['end_time']]],
        ];
        $order_id_arr = Db::name('order')->where($orderWhere)->column('id');
        if ($order_id_arr) {
            $orderGoodsWhere = [['prom_id','=',$this->flashSale['id']],['prom_type','=',2],['order_id','in',implode(',', $order_id_arr)]];
            $goods_num = DB::name('order_goods')->where($orderGoodsWhere)->sum('goods_num');
            if($goods_num){
                return $goods_num;
            }else{
                return 0;
            }
        } else {
            return 0;
        }
    }

    /**
     * 获取用户剩余抢购商品数量
     * @param $user_id
     * @return float|int|mixed
     * User: Jomlz
     */
    public function getUserFlashResidueGoodsNum($user_id){
        $purchase_num = $this->getUserFlashOrderGoodsNum($user_id); //用户抢购已购商品数量
        $residue_num = $this->flashSale['goods_num'] - $this->flashSale['buy_num']; //剩余库存
        //限购》已购
        $residue_buy_limit = $this->specGoodsPrice['limited_quantity'] - $purchase_num;
        if($residue_buy_limit > $residue_num){
            return $residue_num;
        }else{
            return $residue_buy_limit;
        }
    }

    /**
     * 获取单个抢购活动
     * @return static
     */
    public function getPromModel(){
        return $this->flashSale;
    }

    /**
     * 获取商品原始数据
     * @return static
     */
    public function getGoodsInfo()
    {
        return $this->goods;
    }

    /**
     * 获取商品转换活动商品的数据
     * @return static
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
        $activityGoods['activity_title'] = $this->flashSale['title'];
//        $activityGoods['market_price'] =$this->goods['market_price'];
        $activityGoods['price'] = $this->specGoodsPrice['spec_price']['price'];
        $activityGoods['store_count'] = $this->specGoodsPrice['spec_price']['store_count'];
        $activityGoods['start_time'] = $this->flashSale['start_time'];
        $activityGoods['end_time'] = $this->flashSale['end_time'];
        $activityGoods['buy_limit'] = $this->specGoodsPrice['limited_quantity'];
        $activityGoods['virtual_num'] =0;
        return $activityGoods;
    }

    /**
     * 该活动是否已经失效
     */
    public function IsAble(){
        if(empty($this->flashSale)){
            return false;
        }
        if($this->flashSale['is_end'] == 1){
            return false;
        }
        if($this->flashSale['buy_num'] >= $this->flashSale['goods_num']){
            return false;
        }
        if(time() > $this->flashSale['end_time']){
            return false;
        }
        return true;
    }

    /**
     * 抢购商品立即购买
     * @param $buyGoods
     * @return mixed
     * @throws Exception
     */
    public function buyNow($buyGoods){
        if($this->checkActivityIsAble()){
            if($buyGoods['goods_num'] > $this->specGoodsPrice['limited_quantity']){
                throw new Exception('抢购商品立即购买', 0, ['status' => 0, 'msg' => '每人限购'.$this->flashSale['buy_limit'].'件', 'result' => '']);
            }
        }
        $userFlashOrderGoodsNum = $this->getUserFlashOrderGoodsNum($buyGoods['user_id']); //获取用户抢购已购商品数量
        $userBuyGoodsNum = $buyGoods['goods_num'] + $userFlashOrderGoodsNum;
        if($userBuyGoodsNum > $this->flashSale['buy_limit']){
            throw new Exception('抢购商品立即购买', 0, ['status' => 0, 'msg' => '每人限购'.$this->flashSale['buy_limit'].'件，您已下单'.$userFlashOrderGoodsNum.'件', 'result' => '']);
        }
        $flashSalePurchase = $this->flashSale['goods_num'] - $this->flashSale['buy_num'];//抢购剩余库存
        if($buyGoods['goods_num'] > $flashSalePurchase){
            throw new Exception('抢购商品立即购买', 0, ['status' => 0, 'msg' => '商品库存不足，剩余'.$flashSalePurchase, 'result' => '']);
        }
        $buyGoods['member_goods_price'] = $this->flashSale['price'];
        $buyGoods['prom_type'] = 1;
        $buyGoods['prom_id'] = $this->flashSale['id'];
        return $buyGoods;
    }
}