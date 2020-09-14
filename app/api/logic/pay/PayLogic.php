<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * 支付逻辑
 */

namespace app\api\logic\pay;

use app\api\controller\pay\PayNotify;
use app\common\model\order\OrderGoodsModel;
use app\common\model\order\OrderModel;
use app\common\model\PayLogModel;
use app\common\model\user\UserModel;
use app\common\model\user\UserRechargeLogModel;
use sakuno\utils\JsonUtils;
use think\facade\Db;
use WeChatApplets\WeChatPayment;

class PayLogic
{
    protected $logModel;
    public function __construct(PayLogModel $logModel)
    {
        $this->logModel = $logModel;
    }
    /**
     * 参数
     * log_id  支付记录log_id
     * order_sn  支付记录order_sn
     * openid  用户openid
     * */
    public function getPayParameter($param = [])
    {
        $pay_log = $this->logModel->where(['log_id'=>$param['log_id'],'order_sn'=>$param['order_sn'],'is_pay'=>0])->find();
        if (empty($pay_log)){
            return ['status'=>0,'msg'=>'请求参数错误'];
        }
        $pay_mode = $param['pay_mode'];
        $this->logModel->where(['log_id'=>$param['log_id']])->save(['pay_mode'=>$pay_mode]);
        $pay_log = $pay_log->toArray();
        //查询订单信息
        switch ($pay_log['pay_type']){
            case 1: //商品支付
                $pay_order = (new OrderModel())
                    ->where(['id'=>$pay_log['order_id'],'order_sn'=>$pay_log['order_sn']])
                    ->where([['pay_status','<>',1]])
                    ->find();
                //如果是预售订单，支付尾款
                if($pay_order['prom_types'] == 3){
                    if($pay_order['pay_status'] == 2){
                        $pre_sell_info = Db::name('activity')->where(array('id'=>$pay_order['prom_ids']))->find();
                        if($pre_sell_info['final_payment_start_time'] > time()){
                            return ['status'=>0,'msg'=>'还未到支付尾款时间'.date('Y-m-d H:i:s',$pre_sell_info['final_payment_start_time'])];
                        }
                        if($pre_sell_info['final_payment_end_time'] < time()){
                            return ['status'=>0,'msg'=>'对不起，该预售商品已过尾款支付时间'.date('Y-m-d H:i:s',$pre_sell_info['final_payment_end_time'])];
                        }
                    }
                }
                break;
            case 2://充值支付
                $pay_order = (new UserRechargeLogModel())
                    ->where(['id'=>$pay_log['order_id'],'order_sn'=>$pay_log['order_sn'],'status'=>2,'money'=>$pay_log['pay_amount']])
                    ->find();
        }

        if (empty($pay_order)){
            return ['status'=>0,'msg'=>'订单参数错误'];
        }

        $WeChatPayment = new WeChatPayment();
        //支付方式
        switch ($pay_mode){
            case 1: //微信
                //获取微信支付配置参数
                $config = Db::name('website_config')->where('config_type', 'wechat')->select()->toArray();
                //测试配置
                $json = '{"appId":"wx1af1c65cdf73523dA","mch_id":"1566200111","key":"73415497e8b30e7a48e31465c7e103b3","appsecret":"174a30a4272b36829dd73c7b632461c7"}';

                //支付说明
                $body = '支付';
                //平台订单号
                $out_trade_no = $pay_log['order_sn'];

                //支付金额(乘以100)
                $total_fee =  $pay_log['pay_amount'];

                //回调地址
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . "/api/pay_notify/pay_type/{$pay_log['pay_type']}/pay_mode/1";
                //小程序支付
                $payOrderInfo = $WeChatPayment->appWxPay($out_trade_no, $body, $total_fee,$notify_url);
//                $payOrderInfo = $WeChatPayment->appletsUnifiedOrder($out_trade_no, $body, $total_fee, $param['openid'],$notify_url);
                if (isset($payOrderInfo['paySign'])) {
                    $payOrderInfo['order_id'] = $out_trade_no;
                    $payOrderInfo['pay_price_total'] = $total_fee;
                    $res = array('status' => 1,'msg'=>'获取成功','prepay_order' => $payOrderInfo);
                } else {
                    $res = array('status' => 0,'msg' => $payOrderInfo);
                }
                break;
            case 3: //余额
                $pay_password = $this->param['pay_password'] ?? '123';
                $user_id = $pay_log['user_id'];
                $user = (new UserModel())->with(['UserDetails'])->where(['id' => $user_id])->find()->toArray();
                $user_pay_password = $user['UserDetails']['pay_password'];

//                if ($pay_password != $user_pay_password){
//                    return ['status' => 0,'msg'=>'支付密码错误'];
//                }
//                if ($user['UserDetails']['use_money'] < $pay_log['pay_amount']) {
//                    return ['status' => 0,'msg'=>'余额不足'];
//                }
                $data = ['pay_type'=>1,'pay_mode'=>3,'pay_log'=>$pay_log];
                $res = (new PayNotify())->pay_notify($data);
                if ($res['status'] == 1){
                    return ['status' => 1,'msg'=>'支付成功'];
                }else{
                    return ['status' => 1,'msg'=>'支付失败'];

                }
                break;
            default:
                $res = array('status' => 0,'msg' => '未知支付方式');
        }
        return $res;
    }
}