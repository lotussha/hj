<?php


namespace app\common\logic\user;

//用户钱包
use app\common\model\user\UserCommissionLogModel;
use app\common\model\user\UserDetailsModel;
use sakuno\utils\JsonUtils;

class UserWalletLogic
{
    /**
     * 用户钱包
     * User: hao  2020-8-27
     */
    public function wallet($uid)
    {
        $user_details = (new UserDetailsModel())->findInfo(['uid' => $uid], 'commission_use_money,use_integral,use_money,cash_money');
        $user_money = (new UserDetailsLogic())->get_cash(['uid' => $uid]);

        $data = array();
        $data['commission_use_money'] = $user_details['commission_use_money'];
        $data['use_integral'] = $user_details['use_integral'];
        $data['cash_money'] = $user_details['cash_money'];
        $data['use_money'] = $user_details['use_money'];
        $data['qte_money'] = $user_money['recharge_money'] + $user_money['commission_money'];

        //当天
        $start_day = strtotime(date("Y-m-d", time()));
        $end_day = $start_day + 60 * 60 * 24 - 1;

        //当月
        $y = date("Y", time()); //年
        $m = date("m", time()); //月
        $t0 = date('t'); // 本月一共有几天
        $start_month = mktime(0, 0, 0, $m, 1, $y); // 创建本月开始时间
        $end_month = mktime(23, 59, 59, $m, $t0, $y); // 创建本月结束时间
        $where1 = array();
        $where1[] = ['create_time','>',$start_day];
        $where1[] = ['create_time','<',$end_day];
        $where1[] = ['uid','=',$uid];
        $where1[] = ['type','=','103'];
        $data['day_money'] = (new UserCommissionLogModel())->statInfo($where1,'sum','money');

        $where2 = array();
        $where2[] = ['create_time','>',$start_month];
        $where2[] = ['create_time','<',$end_month];
        $where2[] = ['uid','=',$uid];
        $where2[] = ['type','=','103'];
        $data['month_money'] = (new UserCommissionLogModel())->statInfo($where2,'sum','money');

        return JsonUtils::successful('操作成功',$data);
    }
}