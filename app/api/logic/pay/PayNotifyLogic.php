<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/24
 * Time: 17:16
 */

namespace app\api\logic\pay;

use app\common\logic\shop\ShopFinanceLogic;
use app\common\logic\user\UserAccountLogLogic;
use app\common\logic\user\UserMethodLogic;
use app\common\model\GoodsSpecPriceModel;
use app\common\model\order\OrderGoodsModel;
use app\common\model\order\OrderModel;
use app\common\model\PayLogModel;
use app\common\model\user\UserBalanceLogModel;
use app\common\model\user\UserDetailsModel;
use app\common\model\user\UserModel;
use app\common\model\user\UserRechargeCashModel;
use app\common\model\user\UserRechargeLogModel;
use app\common\model\user\UserRechargeUseLogModel;
use think\facade\Db;

class PayNotifyLogic
{

    /**
     * 商品订单支付回调处理
     * User: Jomlz
     */
    public function goodsOrderPayNotify($paylog = [])
    {
        $orderModel = new OrderModel();
        $payModel = new PayLogModel();
        $orderGoods = new OrderGoodsModel();
        $specModel = new GoodsSpecPriceModel();
        $userModel = new UserModel();
        $paylogs[] = $paylog;
        if ($paylog['parent_id'] == 0) {
            $p_paylog = $payModel->where(['parent_id' => $paylog['log_id']])->select()->toArray();
            $paylogs = array_merge($paylogs, $p_paylog);
        }
        dump($paylogs);die;
        $user_id = $paylog['user_id'];
        $order_ids = '';
        foreach ($paylogs as $k => $v) {
            //获取订单表信息
            $order = $orderModel->where([['id', '=', $v['order_id'], ['pay_status', '<>', 1]]])->find()->toArray();
            if (!$order) {
                return false;
            }
            dump($order);die;
            $up_data = ['pay_time' => time(), 'pay_status' => 1];
            $order_ids .= $order['id'] . ',';
            if ($order['prom_types'] == 3) {
                if ($order['pay_status'] == 0) {
                    $up_data['pay_status'] = 2;
                }
                if ($order['pay_status'] == 2) {
                    $up_data['pay_status'] = 1;
                }
            }
            dump($order_ids);die;
            //修改订单
//            $orderModel->where([['id','=',$v['order_id']]])->save($up_data);
            //发送系统消息

            //发送短信

            //获取订单商品信息
            $order_goods = $orderGoods->where([['order_id', '=', $order['id']]])->select()->toArray();
            if ($order_goods) {
                //获取购买者的上两级用户信息
                foreach ($order_goods as $orderGoodsKey => $orderVal) {
//                    //减去规格表库存
//                    Db::name('goods_spec_price')->where(['item_id' => $orderVal['item_id']])->dec('store_count', $orderVal['goods_num'])->update();
//                    //减去商品表总库存
//                    Db::name('goods')->where(['goods_id' => $orderVal['goods_id']])->dec('store_count', $orderVal['goods_num'])->update();
//                    //增加活动表购买数
//                    switch ($orderVal['prom_type']) {
//                        case 1:
//                            break;
//                        case 2:
//                            Db::name('activity')->where(['id' => $orderVal['prom_id']])->inc('buy_num', $orderVal['goods_num'])->update();
//                            break;
//                    }
                    //记录用户分销
                    $userModel->distribution($user_id,$orderVal);
                    //记录门店收入明细
//                    (new ShopFinanceLogic())->addShopFinance($orderVal);
                    //记录用户账单明细
//                    (new UserAccountLogLogic())->userAccountLogAdd($user_is,1,$orderVal,'下单消费',$v['pay_mode']);
                    //增加用户积分

                    //计算用户等级
                    //用户等级记录，完成订单后再记录
//                    $data = ['order_sn'=>$orderVal['order_sn'],'money'=>$orderVal['final_price'],'uid'=>$user_is];
//                    $completion = (new UserMethodLogic())->order_completion($data);
                }
            }


        }
        die;
    }

    /**
     * 充值支付回调处理
     * User: hao 2020.08.24
     */
    public function rechargePayNotify($callBackInfo = [])
    {
        $UserRecharge = new UserRechargeLogModel();
        //充值数据
        $recharge = $UserRecharge->findInfo(['order_sn' => $callBackInfo['out_trade_no']]);
        if (!$recharge) {
            return ['code' => false];
        }
        if ($recharge['status'] == 1) {
            return ['code' => true];
        }
        //金额对不上
//        if ($callBackInfo['total_fee']!=$recharge['money']){
//            return ['code' => false];
//        }


        //可以提现记录
        $UserRechargeCashModel = new UserRechargeCashModel();
        $recharge_cash = $UserRechargeCashModel->where(['uid' => $recharge['uid']])->find();
        //存在用户
        if ($recharge_cash) {
            $data_cash = array();
            $data_cash['money'] = $recharge['money'] + $recharge_cash['money'];
            $data_cash['time'] = time();
            $res = $UserRechargeCashModel->where(['uid' => $recharge['uid']])->update($data_cash);
        } else {
            //不存在用户
            $data_cash = array();
            $data_cash['uid'] = $recharge['uid'];
            $data_cash['money'] = $recharge['money'];
            $data_cash['time'] = time();
            $res = $UserRechargeCashModel->insert($data_cash);
        }
        if (!$res) {
            return ['code' => false];
        }

        //改变用户表
        $user_list = (new UserDetailsModel())->findInfo(['uid' => $recharge['uid']], 'sum_money,use_money,recharge_money,give_money');
        $user_data = array();
        $user_data['sum_money'] = $user_list['sum_money'] + $recharge['money'] + $recharge['give_money'];  //总收益
        $user_data['use_money'] = $user_list['use_money'] + $recharge['money'] + $recharge['give_money'];  //可使用
        $user_data['recharge_money'] = $user_list['recharge_money'] + $recharge['money'];  //充值总金额
        $user_data['give_money'] = $user_list['give_money'] + $recharge['give_money'];  //赠送总金额
        $res = (new UserDetailsModel())->updateInfo(['uid' => $recharge['uid']], $user_data);

        if (!$res) {
            return ['code' => false];
        }

        //修改充值记录表
        $updateData = array();
        $updateData['status'] = 1;
        $updateData['pay_time'] = time();
        $updateData['original_money'] = $user_list['use_money'];
        $updateData['now_money'] = $user_data['use_money'];
        $res = $UserRecharge->updateInfo(['order_sn' => $callBackInfo['out_trade_no']], $updateData);
        if (!$res) {
            return ['code' => false];
        }


        //增加用户使用充值表 (充值使用记录表)
        $data_use = array();
        $data_use['order_sn'] = $callBackInfo['out_trade_no'];
        $data_use['uid'] = $recharge['uid'];
        $data_use['money'] = $recharge['total_money'];
        $data_use['type'] = 1;  //1、充值金额（冲值+赠送）
        $data_use['identity_id'] = $recharge['identity_id'];
        $data_use['recharge_id'] = $recharge['id'];
        $data_use['remarks'] = '用户充值金额';
        $data_use['recharge_id'] = $recharge['id'];
        $data_use['original_money'] = $user_list['use_money'];  //原来金额
        $data_use['now_money'] = $user_data['use_money']; //现在金额
        $res = (new UserRechargeUseLogModel())->addInfo($data_use);
        if (!$res) {
            return ['code' => false];
        }

        //增加用户余额记录表
        $res = (new UserBalanceLogModel)->addBalance($recharge['uid'], 1, $recharge['total_money'], $user_list['use_money'], $user_data['use_money'], $callBackInfo['out_trade_no']);
        if (!$res) {
            return ['code' => false];
        }

        return ['code' => true];
    }

}