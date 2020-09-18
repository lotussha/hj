<?php


namespace app\common\logic\user;


use app\common\model\config\CodeGainModel;
use app\common\model\config\RechargeOptionModel;
use app\common\model\config\WebsiteConfigModel;
use app\common\model\PayLogModel;
use app\common\model\user\UserBalanceLogModel;
use app\common\model\user\UserBankModel;
use app\common\model\user\UserCashModel;
use app\common\model\user\UserCommissionLogModel;
use app\common\model\user\UserDetailsModel;
use app\common\model\user\UserCollectModel;
use app\common\model\user\UserGradeModel;
use app\common\model\user\UserIntegralLogModel;
use app\common\model\user\UserModel;
use app\common\model\user\UserRechargeCashModel;
use app\common\model\user\UserRechargeLogModel;
use app\common\model\user\UserRechargeUseLogModel;
use app\common\model\user\UserSignModel;
use sakuno\utils\JsonUtils;
use Symfony\Component\HttpFoundation\FileBag;
use think\facade\Db;
use think\response\Json;
use WeChatApplets\WeChatPayment;

//小程序用户事物层
class UserDetailsLogic
{
    /**
     * 修改用资料
     * User: hao  2020-8-22
     */
    public function edit_information($receive)
    {
        $data = array();

        //修改个性签名
        if (isset($receive['personal_signature'])) {
            if (strlen($receive['personal_signature']) > 255) {
                return JsonUtils::fail('个性签名不能超过255字符');
            }
            $data['personal_signature'] = $receive['personal_signature'];
        }

        //修改性别
        if (isset($receive['sex'])) {
            if (!in_array($receive['sex'], ['0', '1', '2'])) {
                return JsonUtils::fail('性别有误');
            }
            $data['sex'] = $receive['sex'];
        }

        //修改生日
        if (isset($receive['birthday_time'])) {
            $data['birthday_time'] = strtotime($receive['birthday_time']);
        }

        $model = new UserDetailsModel();
        $rs = $model->updateInfo(['uid' => $receive['uid']], $data);
        if (!$rs) {
            return JsonUtils::fail('操作失败');
        }
        return JsonUtils::successful('操作成功');
    }

    /**
     * 分享二维码
     * User: hao  2020-8-22
     */
    public function share($receive)
    {
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $this->getWeChatAccessToken();
        $param = [
            'page' => $receive['path'], //页面路径
            'scene' => $receive['urlParam'], //携带的参数
            'width' => 280, //宽度 目前最小限制是280
            'auto_color' => false,
        ];
        $param = json_encode($param);
        $res = @$this->sendPost($url, $param);

        $result = json_decode($res, true);
        if (isset($result['errcode'])) {
            return JsonUtils::fail('获取二维码失败:' . $result['errmsg']);
        }

        $result = $this->data_uri($result, 'image/png');
        return JsonUtils::successful('操作成功', ['img' => $result]);
    }


    /**
     * 获取微信的access_token
     * User: hao  2020-8-22
     */
    public function getWeChatAccessToken()
    {
        $website = new WebsiteConfigModel();
        $website_list = $website->where('type', 'in', 'xiaoappid,app_secret,')->field('type,val')->column('val', 'type');

        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $website_list['xiaoappid'] . "&secret=" . $website_list['app_secret']; //获取access_token的Url拼接
        $res = curlHttp($url);
        if (!empty($res['access_token'])) {
            return $res['access_token'];
        }
        return false;
    }

    // 发送post请求
    public function sendPost($url, $data)
    {
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检测
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Expect:')); //解决数据包大不能提交
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Errno' . curl_error($curl);
        }
        curl_close($curl); // 关键CURL会话
        return $tmpInfo; // 返回数据
    }

    //二进制转图片image/png
    public function data_uri($contents, $mime)
    {
        $base64 = base64_encode($contents);
        return ('data:' . $mime . ';base64,' . $base64);
    }

    /**
     * 充值
     * User: hao  2020-8-22
     */
    public function recharge($receive)
    {

        $order_sn = uniqueNumber();

        //充值列表
        $rechargeOption = new RechargeOptionModel();

        //查看充值范围
        $where = array();
        $where[] = ['min_money', '<=', $receive['money']];
        $where[] = ['max_money', '>=', $receive['money']];
        $where[] = ['status', '=', 1];
        $where[] = ['is_delete', '<>', 1];
        $option = $rechargeOption->findInfo($where);

        //充值记录
        $msg_data = array();
        $msg_data['uid'] = $receive['uid'];
        $msg_data['order_sn'] = $order_sn;
        $msg_data['money'] = $receive['money'];
        $msg_data['give_money'] = 0;

        if ($option) {
            $msg_data['give_money'] = sprintf("%.2f", $receive['money'] * $option['give'] / 100);
            $msg_data['log_json'] = json_encode($option, 320);
        }

        $msg_data['identity_id'] = $receive['identity_id'] ?? '0';
        $msg_data['not_used_money'] = $receive['money'] + $msg_data['give_money'];
        $msg_data['total_money'] = $receive['money'] + $msg_data['give_money'];
        $msg_data['qte_money'] = $receive['money'];

        //门店待返金额 %
        $commission_scale = (new WebsiteConfigModel())->getValues(['type' => 'recharge_commission_scale'], 'val');
        $total_rebate = $commission_scale * $msg_data['total_money'] / 100;
        $msg_data['total_rebate'] = $total_rebate;
        $msg_data['stay_rebate'] = $total_rebate;
        $msg_data['recharge_commission_scale'] = $commission_scale;

        $UserRechargeLog = new UserRechargeLogModel();

        try {
            $UserRechargeLog->beginTrans();
            //添加充值记录
            $id = $UserRechargeLog->addInfoId($msg_data);
            if (!$id) {
                $UserRechargeLog->rollbackTrans();
                return JsonUtils::fail('操作失败');
            }

            //添加订单记录
            $payLog = new PayLogModel();
            $log_id = $payLog->insertPayLog($id, $order_sn, $receive['money'], '2', $receive['uid'], 0, 0, '用户充值');
            if (!$log_id) {
                $UserRechargeLog->rollbackTrans();
                return JsonUtils::fail('操作失败');
            }
            $UserRechargeLog->commitTrans();
            return JsonUtils::successful('操作成功', ['log_id' => $log_id, 'order_sn' => $order_sn]);

        } catch (\Exception $e) {
            $UserRechargeLog->rollbackTrans();
            return JsonUtils::fail('操作失败' . $e);
        }


    }


    /**
     * 获取提现数据
     * User: hao  2020-8-22
     */
    public function get_cash($receive)
    {

        //可提金额
        $qte_cash = (new UserRechargeCashModel())->findInfo(['uid' => $receive['uid']]);

        //可提现间隔时间 与手续费
        $website = new WebsiteConfigModel();
        $cash_time = $website->where('type', '=', 'cash_time')->value('val');
        $times = $cash_time * 24 * 60 * 60;
        $new_time = time();
        $money = 0;
        if (($new_time - $times) > $qte_cash['time']) {
            $money = $qte_cash['money'];
        }
        $grade = array();
        if (isset($receive['grade_id'])) {
            $grade = (new UserGradeModel())->findInfo(['id' => $receive['grade_id']], 'cash_lowest,cash_upper,cash_charge,cash_num');
        }

        $grade['recharge_money'] = $money;
        $grade['commission_money'] = (new UserDetailsModel())->getValues(['uid' => $receive['uid']], 'commission_use_money');
        return $grade;

    }

    /**
     * 提现
     * User: hao  2020-8-25
     */
    public function cash($receive)
    {
        $get_cash = $this->get_cash($receive);

        //1：充值金额提现   2：佣金提现
        if ($receive['cash_mode'] == 1) {
            if ($get_cash['recharge_money'] == 0) {
                return JsonUtils::fail('没有提现额度');
            }
            if ($get_cash['recharge_money'] < $receive['money']) {
                return JsonUtils::fail('您的可提现为：' . $get_cash['money'] . '元，不能超过该额度');
            }

        } else {
            if ($get_cash['commission_money'] == 0) {
                return JsonUtils::fail('没有提现额度');
            }
            if ($get_cash['commission_money'] < $receive['money']) {
                return JsonUtils::fail('您的可提现为：' . $get_cash['money'] . '元，不能超过该额度');
            }

        }
        if ($receive['money'] < $get_cash['cash_lowest']) {
            return JsonUtils::fail('提现不能低于' . $get_cash['cash_lowest']);
        }

        if ($receive['money'] > $get_cash['cash_upper']) {
            return JsonUtils::fail('提现不能高于' . $get_cash['cash_upper']);
        }


        $today = strtotime(date('Y-m-d', time()));
        $where = array();
        $where[] = ['uid', '=', $receive['uid']];
        $where[] = ['examine_is', 'in', '1,3'];
        $where[] = ['create_time', '>', $today];
        $num = (new UserCashModel())->statInfo($where);
        if ($num > $get_cash['cash_num']) {
            return JsonUtils::fail('今天没有提现次数');
        }

        //提现数据
        $data_cash = array();
        $data_cash['uid'] = $receive['uid'];
        $data_cash['order_sn'] = uniqueNumber();
        $data_cash['money'] = $receive['money'];
        $charge = $receive['money'] * $get_cash['cash_charge'] / 100;
        $data_cash['charge'] = sprintf("%.2f", $charge);
        $data_cash['account_money'] = $data_cash['money'] - $data_cash['charge'];
        $data_cash['type'] = $receive['cash_type'];
        $data_cash['cash_mode'] = $receive['cash_mode'];

        //1：充值金额提现   2：佣金提现
        if ($receive['cash_mode'] == 1) {
            $user_money = (new UserDetailsModel())->getValues(['uid' => $receive['uid']], 'use_money'); //现在可使用充值金额
        } else {
            $user_money = (new UserDetailsModel())->getValues(['uid' => $receive['uid']], 'commission_use_money'); //现在可提现佣金金额
        }
        $data_cash['original_money'] = $user_money;
        $data_cash['now_money'] = $user_money - $receive['money'];


        //提现方式
        //微信
        if ($receive['cash_type'] == 1) {
            if (!isset($receive['wx_img_url'])) {
                return JsonUtils::fail('微信收款二维码图片不能为空');
            }
            if (!isImg($receive['wx_img_url'])) {
                return JsonUtils::fail('微信收款二维码图片格式不正确');
            }
            $data_cash['wx_img_url'] = $receive['wx_img_url'];
        }
        //支付宝
        if ($receive['cash_type'] == 2) {
            if (!isset($receive['alipay_username']) || !isset($receive['idcard_user'])) {
                return JsonUtils::fail('支付宝账号和真实名称不能为空');
            }

            $data_cash['alipay_username'] = $receive['alipay_username'];
            $data_cash['idcard_user'] = $receive['idcard_user'];
        }

        //银行卡
        if ($receive['cash_type'] == 3) {
            if (!isset($receive['card'])) {
                return JsonUtils::fail('卡号不能为空');
            }
            $card_list = (new UserBankModel())->findInfo(['id' => $receive['card'], 'uid' => $receive['uid']]);
            if (!$card_list) {
                return JsonUtils::fail('该卡号不存在');
            }
            $data_cash['card_json'] = json_encode($card_list, 320);
            $data_cash['card'] = $card_list['card'];
        }

        try {
            Db::startTrans();
            //加入提现数据库
            $id = (new UserCashModel())->addInfoId($data_cash);

            if (!$id) {
                return JsonUtils::fail('操作失败，添加数据有误');
            }

            //1：充值金额提现   2：佣金提现
            if ($receive['cash_mode'] == 1) {
                //扣除用户详情金额
                $use_money = (new UserDetailsModel())->getValues(['uid' => $receive['uid']], 'use_money'); //现在金额
                $res = (new UserDetailsModel())->setDataDec(['uid' => $receive['uid']], 'use_money', $receive['money']);

                if (!$res) {
                    Db::rollback();
                    return JsonUtils::fail('操作失败，扣除用户详情金额有误');
                }

                //修改可提现记录
                $res = (new UserRechargeCashModel())->setDataDec(['uid' => $receive['uid']], 'money', $receive['money']);

                if (!$res) {
                    Db::rollback();
                    return JsonUtils::fail('操作失败，修改可提现记录有误');
                }

                //余额记录
                $now_money = $use_money - $receive['money'];
                $res = (new UserBalanceLogModel())->addBalance($receive['uid'], '2', $receive['money'], $use_money, $now_money, $data_cash['order_sn']);
                if (!$res) {
                    Db::rollback();
                    return JsonUtils::fail('操作失败，余额记录有误');
                }

                //扣除充值表的可提现金额
                $res = $this->use_recharge($receive['money'], $receive['uid'], $use_money, $id);
                if ($res === false) {
                    Db::rollback();
                    return JsonUtils::fail('操作失败，扣除充值表的可提现金额有误');
                }
            } else {
                //扣除用户详情金额
                $commission_money = (new UserDetailsModel())->findInfo(['uid' => $receive['uid']], 'commission_use_money,commission_frozen_money'); //现在金额
                $res = (new UserDetailsModel())->setDataDec(['uid' => $receive['uid']], 'commission_use_money', $receive['money']);
                if (!$res) {
                    Db::rollback();
                    return JsonUtils::fail('操作失败，扣款用户佣金金额有误');
                }
                $commission_data = array();
                $commission_data['uid'] = $receive['uid'];
                $commission_data['money'] = '-' . $receive['money'];
                $commission_data['order_sn'] = $data_cash['order_sn'];
                $commission_data['commission_status'] = 1;
                $commission_data['type'] = 203;
                $commission_data['remark'] = '用户佣金提现' . $receive['money'];
                $commission_data['original_money'] = $commission_money['commission_use_money'];
                $commission_data['now_money'] = $commission_money['commission_use_money'] - $receive['money'];
                $commission_data['original_frozen_money'] = $commission_money['commission_frozen_money'];
                $commission_data['now_frozen_money'] = $commission_money['commission_frozen_money'];
                $res = (new UserCommissionLogModel())->addInfo($commission_data);
                if (!$res) {
                    Db::rollback();
                    return JsonUtils::fail('操作失败,写入佣金记录表失败');
                }
            }


            Db::commit();
            return JsonUtils::successful('操作成功');

        } catch (\Exception $e) {
            Db::rollback();
            return JsonUtils::fail('操作失败，服务器异常');
        }

    }


    //扣除充值表的可提现金额
    public function use_recharge($money, $uid, $use_money, $id = 0)
    {
        //扣除充值表
        $where = array();
        $Where[] = ['status', '=', 1];
        $where[] = ['uid', '=', $uid];
        $where[] = ['qte_money', '>', 0];
        $where[] = ['used_is', '=', 1];
        $list = (new UserRechargeLogModel())->where($where)->order('create_time asc')->find();

        if (!$list) {
            return false;
        }

        //剩下的钱
        $over_money = $money - $list['qte_money'];

        if ($over_money > 0) {
            //剩下的钱大于0 ，在重新调用
            $not_used_money = $list['not_used_money'] - $list['qte_money']; //可消费金额
            $res = (new UserRechargeLogModel())->updateInfo(['id' => $list['id']], ['qte_money' => 0, 'not_used_money' => $not_used_money]);
            if (!$res) {
                return false;
            }
            //加入充值使用数据 (充值使用记录表)
            $data_use = array();
            $data_use['order_sn'] = $list['order_sn'];
            $data_use['uid'] = $list['uid'];
            $data_use['money'] = '-' . $list['qte_money'];
            $data_use['type'] = 2;
            $data_use['identity_id'] = $list['identity_id'];
            $data_use['remarks'] = '用户提现金额';
            $data_use['recharge_id'] = $list['id'];
            $data_use['original_money'] = $use_money;  //原来金额
            $data_use['now_money'] = $use_money - $list['qte_money'];   //现在金额
            $data_use['cash_id'] = $id;

            $res = (new UserRechargeUseLogModel())->addInfo($data_use);
            if (!$res) {
                return false;
            }
            return $this->use_recharge($over_money, $uid, $data_use['now_money']);
        } else {
            //负数或者0，不再重新调用
            $qte_money = $list['qte_money'] - $money;  //可提金额
            $not_used_money = $list['not_used_money'] - $money; //可消费金额
            $res = (new UserRechargeLogModel())->updateInfo(['id' => $list['id']], ['qte_money' => $qte_money, 'not_used_money' => $not_used_money]);
            if (!$res) {
                return false;
            }
            //加入充值使用数据
            $data_use = array();
            $data_use['order_sn'] = $list['order_sn'];
            $data_use['uid'] = $list['uid'];
            $data_use['money'] = '-' . $money;
            $data_use['type'] = 2;
            $data_use['identity_id'] = $list['identity_id'];
            $data_use['remarks'] = '用户提现金额';
            $data_use['recharge_id'] = $list['id'];
            $data_use['original_money'] = $use_money;  //原来金额
            $data_use['now_money'] = $use_money - $money;   //现在金额
            $data_use['cash_id'] = $id;

            $res = (new UserRechargeUseLogModel())->addInfo($data_use);
            if (!$res) {
                return false;
            }
            return true;
        }

    }

    /**
     * 个人中心
     * User: hao  2020-8-27
     */
    public function personal($uid)
    {
        $data = array();
        $user = (new UserModel())->findInfo(['id' => $uid], 'id,grade_id,team_num,nick_name,avatar_url,username');
        $data['grade_id'] = $user['grade_id'];  //等级
        $data['team_num'] = $user['team_num'];//团队人数
        $data['id_num'] = (new UserModel())->fictitiousId($user['id']);//ID
        $data['nick_name'] = $user['nick_name'];//昵称
        $data['avatar_url'] = $user['avatar_url'];//头像
        $data['bind_phone'] = $user['username'];  //绑定手机号

        $grade = (new UserGradeModel())->findInfo(['id' => $user['grade_id']], 'name,discount');
        $data['grade_name'] = $grade['name'];//等级名称
        $data['discount'] = $grade['discount'];//折扣
        $collect_num = (new UserCollectModel())->where(['user_id' => $uid, 'type' => 1])->count();
        $data['collect_num'] = $collect_num;//好物圈

        $user_details = (new UserDetailsModel())->findInfo(['uid' => $uid], 'commission_use_money,commission_frozen_money,use_integral,frozen_integral,use_money,personal_signature,sex,birthday_time');

        $data['profit'] = $user_details['commission_use_money'] + $user_details['commission_frozen_money'];  //我的收益
        $data['integral'] = $user_details['use_integral'] + $user_details['frozen_integral'];  //我的积分
        $data['recharge_money'] = $user_details['use_money']; //充值金额
        $data['personal_signature'] = $user_details['personal_signature'];//个性签名
        $data['sex'] = $user_details['sex'];//性别
        $data['birthday_time'] = date('Y-m-d', $user_details['birthday_time']);//生日


        $data['ranking'] = 0;  //排名
        $data['coupon'] = 0; //优惠券

        return JsonUtils::successful('操作成功', $data);

    }


    /**
     * 签到页面
     * User: hao  2020-8-27
     */
    public function sign_page($uid)
    {
        //获取近的年 月 日
        //本月天数
        $month_num = date('t');
        //本年
        $year = date('Y');
        $month = date('m');
        $day = date('d');

        //今天是否签到
        $is = 0;
        $res = (new UserSignModel())->findInfo(['year' => $year, 'month' => $month, 'day' => $day, 'uid' => $uid]);
        if ($res) {
            $is = 1;
        }

        $list_sign = (new UserSignModel())->getColumn(['year' => $year, 'month' => $month, 'uid' => $uid], 'day');


        $arr = array();
        foreach ($list_sign as $key => $value) {
         $arr[$key]['date'] = $year.'-'.$month.'-'.$value;
         $arr[$key]['info'] = '已打卡';

        }

        $data = array();
        $data['is'] = $is;
        $data['data'] = $arr;
        return JsonUtils::successful('操作成功', $data);

    }

    /**
     * 用户签到
     * User: hao  2020-8-27
     */
    public function sign_user($uid)
    {

        //本年
        $year = date('Y');
        $month = date('m');
        $day = date('d');

        //判断用户是否已经签到
        $res = (new UserSignModel())->findInfo(['year' => $year, 'month' => $month, 'day' => $day, 'uid' => $uid]);
        if ($res) {
            return JsonUtils::fail('已签到');
        }

        //送的积分
        $sign_integral_num = (new WebsiteConfigModel())->getValues(['type' => 'sign_integral_num'], 'val');

        Db::startTrans();
        try {
            //改变用户的积分
            $user_details = (new UserDetailsModel())->findInfo(['uid' => $uid], 'sum_integral,use_integral');
            $sum_integral = $user_details['sum_integral'] + $sign_integral_num;
            $use_integral = $user_details['use_integral'] + $sign_integral_num;
            $res = (new UserDetailsModel())->updateInfo(['uid' => $uid], ['sum_integral' => $sum_integral, 'use_integral' => $use_integral]);
            if (!$res) {
                Db::rollback();
                return JsonUtils::fail('签到失败');
            }

            //添加积分明细
            $data_log = array();
            $data_log['uid'] = $uid;
            $data_log['integral'] = $sign_integral_num;
            $data_log['type'] = 301;
            $data_log['original_integral'] = $user_details['use_integral'];
            $data_log['now_integral'] = $use_integral;
            $data_log['integral_type'] = 2;
            $data_log['remark'] = '用户签到获取积分' . $sign_integral_num;
            $res = (new UserIntegralLogModel())->addInfoId($data_log);
            if (!$res) {
                Db::rollback();
                return JsonUtils::fail('签到失败');
            }
            //添加签到
            $data_sign = array();
            $data_sign['uid'] = $uid;
            $data_sign['integral'] = $sign_integral_num;
            $data_sign['year'] = $year;
            $data_sign['month'] = $month;
            $data_sign['day'] = $day;
            $res = (new UserSignModel())->addInfoId($data_sign);
            if (!$res) {
                Db::rollback();
                return JsonUtils::fail('签到失败');
            }
            Db::commit();
            return JsonUtils::successful('签到成功');
        } catch (\Exception $e) {
            Db::rollback();
            return JsonUtils::fail('签到失败');
        }
    }


    /**
     * 用户团队
     * User: hao  2020-8-27
     */
    public function team($receive)
    {
        //或者第一层
        $list_id1 = (new UserModel())->where(['share_id' => $receive['uid']])->column('id');
        $list_id2 = (new UserModel())->where('share_id', 'in', $list_id1)->column('id');

        $where = array();
        switch ($receive['team_level']) {
            case 0:
                $list_id = array_merge($list_id1, $list_id2);
                $where[] = ['id', 'in', $list_id];
                break;

            case 1:
                $where[] = ['id', 'in', $list_id1];
                break;

            case 2:
                $where[] = ['id', 'in', $list_id2];
                break;
            default:
                return JsonUtils::fail('参数有误');
        }

        $list = (new UserModel())->getUserTeam($where, $receive);

        return JsonUtils::successful('操作成功', $list);


    }


    /**
     * 修改支付密码
     * User: hao  2020.09.09
     */
    public function modify_payment_password($receive){
        //验证密码
        $where_code = array();
        $where_code[] = ['phone','=',$receive['phone']];
        $where_code[] = ['type','=',$receive['code_type']];
        $where_code[] =['code','=',$receive['code']];
        $where_code[] =['start_time','<',time()];
        $where_code[] = ['end_time','>',time()];
        $where_code[] = ['status','=',0];
        $list = (new CodeGainModel())->where($where_code)->order('start_time desc')->find();
        if (!$list){
            return JsonUtils::fail('验证码有误');
        }

        $password =  password_hash($receive['password'], 1);

        $res = (new UserDetailsModel())->where(['uid'=>$receive['uid']])->update(['pay_password'=>$password]);
        if ($res===false){
            return JsonUtils::fail('修改失败');
        }

        return JsonUtils::successful('操作成功');



    }
}