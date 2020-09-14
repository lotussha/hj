<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/9/2
 * Time: 17:44
 */

namespace app\common\logic\shop;

use app\common\model\log\SyslogsModel;
use app\common\model\order\OrderGoodsModel;
use app\common\model\settlement\SettlementModel;
use app\common\model\shop\ShopFinanceModel;

class ShopFinanceLogic
{
    protected $financeModel;
    protected $orderGoodsModel;
    public function __construct()
    {
        $this->financeModel = new ShopFinanceModel();
        $this->orderGoodsModel = new OrderGoodsModel();
    }

    /**
     * 记录门店财务收入
     * User: Jomlz
     */
    public function addShopFinance($order_goods)
    {
//        dump($order_goods);die;
        $finance_info = $this->financeModel->where(['order_id'=>$order_goods['order_id'],'goods_id'=>$order_goods['goods_id']])->find();
        if ($finance_info){
            $data = ['message'=>'当前订单商品已记录过','line'=>(__LINE__) - 2,'file'=>__FILE__,'level'=>2,'bus_explain'=>'记录门店财务收入'];
            (new SyslogsModel())->addLog($data);
            return false;
        }
        $shop_info = (new SettlementModel())->where(['identity'=>$order_goods['identity'],'admin_id'=>$order_goods['identity_id']])->find();
        if (!$shop_info){
            $data = ['message'=>'门店信息不存在','line'=>(__LINE__) - 2,'file'=>__FILE__,'level'=>2,'create_time'=>time(),'bus_explain'=>'记录门店财务收入'];
            (new SyslogsModel())->addLog($data);
            return false;
        }

        //门店实际收入 = 商品实际支付价格-商品1级分销-商品二级分销-平台让利
        $real_income = ($order_goods['final_price'] - $order_goods['one_distribution_price'] - $order_goods['two_distribution_price'] - $order_goods['let_profits_price']) * $order_goods['goods_num'];
        $data = [
            'identity' => $order_goods['identity'],
            'identity_id' => $order_goods['identity_id'],
            'order_id' => $order_goods['order_id'],
            'goods_id' => $order_goods['goods_id'],
            'money' => 0,
            'frozen_money' => $real_income,
            'original_money' => $shop_info['wallet'],
            'original_frozen_money' => $shop_info['wallet_frozen'],
            'order_goods_money' => $order_goods['goods_price'] * $order_goods['goods_num'],
            'source_explain' => '订单收益',
            'status_explain' => '订单已支付，待完成',
            'pay_mode' => 1,
            'type' => 1,
            'add_time' => time(),
        ];
        $res = $this->financeModel->insert($data);
        if (!$res){
            $data = ['message'=>'添加门店财务信息失败','line'=>(__LINE__) - 2,'file'=>__FILE__,'level'=>3,'bus_explain'=>'记录门店财务收入'];
            (new SyslogsModel())->addLog($data);
        }
    }
}