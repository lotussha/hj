<?php


namespace app\api\controller\user;


use app\apiadmin\controller\user\UserRechargeLog;
use app\common\model\PayLogModel;
use app\common\model\user\UserRechargeCashModel;
use app\common\model\user\UserRechargeLogModel;
use think\facade\Db;

class Notify
{

    //    小程序支付回调
    public function applets_callback()
    {
        //获取通知的数据
        $xml = @isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        //测试保存数据(上线注释)
        Db::name('paytext')->insert(['order_id' => $data['out_trade_no'], 'data' => json_encode($data, 320), 'time' => date('Y-m-d H:i:s', time())]);

        // 判断签名是否正确  判断支付状态
        if (@$data['result_code'] == 'SUCCESS' && @$data['return_code'] == 'SUCCESS' && isset($data['out_trade_no']) && isset($data['transaction_id'])) {
            $payLogModel = new PayLogModel();
            $payLogModel->beginTrans();
            $payLog = $payLogModel->findInfo(['order_sn' => $data['out_trade_no']]);

            if ($payLog['is_pay'] == 1) {
                $this->sendWxSuccessXmlV2(true); //已处理过的订单，止步于此
                exit('订单已经完成');
            }
            $log_data = array();
            $log_data['is_pay'] = 1;
            $log_data['pay_time'] = time();
            $res = $payLog->where(['order_sn' => $data['out_trade_no']])->update($log_data);
            if (!$res) {
                $payLogModel->rollbackTrans();
            }

        }

    }

    //用户充值回调
    public function recharge()
    {
        //获取通知的数据
        $xml = @isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        //测试保存数据
        Db::name('paytext')->insert(['order_id' => $data['out_trade_no'], 'data' => json_encode($data, 320), 'time' => date('Y-m-d H:i:s', time())]);

        // 判断签名是否正确  判断支付状态
        if (@$data['result_code'] == 'SUCCESS' && @$data['return_code'] == 'SUCCESS' && isset($data['out_trade_no']) && isset($data['transaction_id'])) {

            try {

                $UserRecharge = new UserRechargeLogModel();
                //充值数据
                $recharge = $UserRecharge->findInfo(['order_id' => $data['out_trade_no']]);

                if ($recharge['pay_status'] > 1) {
                    $this->sendWxSuccessXmlV2(true); //已处理过的订单，止步于此
                    exit('订单已经完成');
                }

                $UserRecharge->beginTrans();

                //修改充值记录表
                $updateData = array();
                $updateData['status'] = 1;
                $updateData['pay_time'] = time();
                $updateData['transaction_id'] = $data['transaction_id'];
                $res = $UserRecharge->updateInfo(['order_id' => $data['out_trade_no']], $updateData);
                if (!$res) {
                    $UserRecharge->rollbackTrans();
                }

                //修改订单号记录表
                $payLog = new PayLogModel();
                $log_data = array();
                $log_data['is_pay'] = 1;
                $log_data['pay_time'] = time();
                $res = $payLog->where(['order_sn' => $data['out_trade_no']])->update($log_data);
                if (!$res) {
                    $UserRecharge->rollbackTrans();
                }

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
                    $UserRecharge->rollbackTrans();
                }


                $UserRecharge->commitTrans();
                $this->sendWxSuccessXmlV2(true); //已处理过的订单，止步于此

                exit('success');

            } catch (\Exception $e) {
                $UserRecharge->rollbackTrans();
            }


        }
    }






    //通知微信，已经处理成功
    //is_echo true为直接输出，false为return
    public function sendWxSuccessXmlV2($is_echo = true)
    {
        $return = ['return_code' => 'SUCCESS', 'return_msg' => 'OK'];
        $xml = '<xml>';
        foreach ($return as $k => $v) {
            $xml .= '<' . $k . '><![CDATA[' . $v . ']]></' . $k . '>';
        }
        $xml .= '</xml>';

        if (!$is_echo) return $xml;

        echo $xml;
    }
}