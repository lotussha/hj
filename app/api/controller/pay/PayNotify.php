<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/24
 * Time: 16:27
 */

namespace app\api\controller\pay;

use app\api\logic\pay\PayNotifyLogic;
use app\common\model\PayLogModel;
use app\Request;
use think\Collection;
use think\facade\Db;
use WeChatApplets\WeChatPayment;

class PayNotify extends Collection
{
    /**
     * 支付回调
     */
    public function pay_notify($param = [])
    {

        $paylog = (new PayLogModel())->findInfo(['log_id'=>23]);
        $res = (new PayNotifyLogic())->goodsOrderPayNotify($paylog);die;
        $request = new Request();

//        $paylog = (new PayLogModel())->findInfo(['log_id'=>23]);
//        $res = (new PayNotifyLogic())->goodsOrderPayNotify($paylog);die;

        $pay_type = $request->param('pay_type') ?? 0; //支付类型
        $pay_mode = $request->param('pay_mode') ?? 0; //支付方式

        $WeChatPayment = new WeChatPayment();

        switch ($pay_mode){
            case 1: //微信
//                $postStr = file_get_contents('php://input');
//                $msg = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                //获取通知的数据
                $xml = @isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");
                $msg = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

                $callBackInfo = array(
                    'call_back' => json_encode($msg),
                    'trade_status' => $msg['result_code'] == 'SUCCESS' ? 1 : -1, //支付状态
                    'out_trade_no' => $msg['out_trade_no'], //商户订单号
                    'total_fee' => $msg['total_fee'] / 100,  //支付金额
                    'gmt_payment' => strtotime($msg['time_end']),     //交易付款时间
                );

                //查询是否真实支付
                $payStatus = $WeChatPayment->orderQuery($msg['out_trade_no'], $msg['total_fee']);
                if ($payStatus===false){
                    $this->sendWxSuccessXml();
                    exit('不是真实支付');
                }
                break;
            case 3: //余额
                //扣除金额
                break;
        }

        //测试保存数
        //据
//        Db::name('paytext')->insert(['order_id' => $callBackInfo['out_trade_no'], 'data' => json_encode($msg, 320), 'time' => date('Y-m-d H:i:s', time())]);

        if (empty($callBackInfo)){
            exit('支付方式错误');
        }

        //已处理
        $paylog = (new PayLogModel())->findInfo(['order_sn'=>$callBackInfo['out_trade_no']]);
        if ($paylog['is_pay']==1){
            $this->sendWxSuccessXml();
            exit('已处理');
        }

        //支付失败
        if ($callBackInfo['trade_status']==-1){
            $this->sendWxSuccessXml();
            exit('支付失败');
        }
        try {
            Db::startTrans();
            //改变订单表
            $paylog_data = array();
            $paylog_data['is_pay'] = 1;
            $paylog_data['pay_time'] = time();
            (new PayLogModel())->updateInfo(['order_sn'=>$msg['out_trade_no']],$paylog_data);

            switch ($pay_type){
                case 1: //商品支付
                    $res = (new PayNotifyLogic())->goodsOrderPayNotify($paylog);
                    break;

                case 2:  //充值支付
                    $res = (new PayNotifyLogic())->rechargePayNotify($callBackInfo);
                    break;
            }

            if ($res['code']===false){
                Db::rollback();
                return ['status'=>0,'msg'=>'error'];
            }else{
                $this->sendWxSuccessXml();
                Db::commit();
                return ['status'=>1,'msg'=>'success'];
            }
            exit();
        }catch (\Exception $e){
            Db::rollback();
            return ['status'=>0,'msg'=>'error'];
            exit();
        }
    }

    //通知微信，已经处理成功
    //is_echo true为直接输出，false为return
    public function sendWxSuccessXml($is_echo=true)
    {
        $return = ['return_code'=>'SUCCESS','return_msg'=>'OK'];
        $xml = '<xml>';
        foreach($return as $k=>$v){
            $xml.='<'.$k.'><![CDATA['.$v.']]></'.$k.'>';
        }
        $xml .='</xml>';

        if (!$is_echo) return $xml;

        echo $xml;
    }



    //模拟充值支付成功
    public function passpay(){
        $callBackInfo = array(
            'trade_status' => 1 , //支付状态
            'out_trade_no' => input('order_sn',''), //商户订单号
            'total_fee' => '20000' / 100,  //支付金额
            'gmt_payment' => time(),     //交易付款时间
        );
        Db::startTrans();
        $res = (new PayNotifyLogic())->rechargePayNotify($callBackInfo);
        if ($res['code']==false){
            Db::rollback();
        }
        dump($res);
        Db::commit();
    }
}