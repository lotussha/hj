<?php


namespace app\common\logic\user;

//use Exception;
use app\common\model\user\RndChinaName;
use app\common\model\user\UserBalanceLogModel;
use app\common\model\user\UserCashModel;
use app\common\model\user\UserCommissionLogModel;
use app\common\model\user\UserEditShareLogModel;
use app\common\model\user\UserModel;
use app\common\model\user\UserRechargeCashModel;
use app\common\model\user\UserRechargeLogModel;
use app\common\model\user\UserRechargeUseLogModel;
use app\common\model\user\UserUpgradeLogModel;
use app\Request;
use app\common\model\user\UserDetailsModel;
use app\common\model\user\UserIntegralLogModel;
use app\common\model\user\UserMoneyLogModel;
use sakuno\utils\JsonUtils;
use think\facade\Db;

//用户逻辑层
class UserLogic
{
    /**
     * 添加假数据处理
     * User: hao
     * Date: 2020/8/14
     */
    public function addhandle($data)
    {

        $data_arr = array();

        // 1:一键生成  2：手动生成
        if ($data['is_touch'] == 2) {
            if (!isset($data['username']) || !isset($data['nick_name'])) {
                return ['data_code' => false, 'data_msg' => '手动生成账号或者昵称不能为空'];
            }
            if (strlen($data['username']) < 11) {
                return ['data_code' => false, 'data_msg' => '虚拟账号要大于11位'];
            }
            $UserModel = new UserModel();
            $rs = $UserModel->where(['username' => $data['username']])->value('id');
            if ($rs) {
                return ['data_code' => false, 'data_msg' => '已有相同的账号'];
            }

            $data_arr['username'] = $data['username'];
            $data_arr['nick_name'] = $data['nick_name'];

        } else {
            $rndName = new RndChinaName();
            $data_arr['nick_name'] = $rndName->getName(); //昵称
            $data_arr['phone'] = getIntMicrotime(); //用毫秒充当手机号
        }

        if (isset($data['share_id'])) {
            $data_arr['share_id'] = $data['share_id'];
        }

        if (isset($data['grade_id'])) {
            $data_arr['grade_id'] = $data['grade_id'];
        }
        if (isset($data['avatar_url'])) {
            $data_arr['avatar_url'] = $data['avatar_url'];
        }
        $data_arr['is_true'] = 2;
        $data_arr['create_time'] = time();
        $data_arr['phone'] = $data_arr['username'];
        return $data_arr;
    }

    /**
     * 团队数据
     * User: hao
     * Date: 2020/8/14
     */
    public function team($arr, $id, $page = 1, $list_row = 10)
    {

        $list = get_downline($arr, $id, 'share_id'); //获取全部团队
        $allId = array_column($list, 'id'); //获取全部id
        $allId = implode(',', $allId);
        $UserModel = new UserModel();
        $where = array();
        $where[] = ['id', 'in', $allId];

        $receive = array();
        $receive['page'] = $page;
        $receive['list_row'] = $list_row;
        $receive['where'] = $where;
        $receive['field'] = '*';

        $lists = $UserModel->getAllUser($receive);

        foreach ($lists['data'] as $key => $value) {
            foreach ($list as $k => $v) {
                if ($v['id'] == $value['id']) {
                    $lists['data'][$key]['level'] = $v['level'];
                }
            }
        }
        return $lists;
    }

    /**
     * 后台增加积分
     * User: hao
     * Date: 2020/8/15
     */
    public function add_integral($id, $integral,$aid,$remark='')
    {
        $UserDetailsModel = new UserDetailsModel();
        $UserIntegralLogModel = new UserIntegralLogModel();

        $userDetails = $UserDetailsModel->findInfo(['uid' => $id], 'sum_integral,use_integral');
        try {
            $UserDetailsModel->beginTrans(); //开始事务
            $sum_integral = $userDetails['sum_integral'] + $integral;  //总积分
            $use_integral = $userDetails['use_integral'] + $integral;  //可使用积分
            //修改积分
            $res = $UserDetailsModel->updateInfo(['uid' => $id], ['sum_integral' => $sum_integral, 'use_integral' => $use_integral]);
            if (!$res) {
                $UserDetailsModel->rollbackTrans();//事务回滚
                return JsonUtils::fail('添加用户积分失败', PARAM_IS_INVALID);
            }

            //记录积分
            $integral_data = array();
            $integral_data['uid'] = $id;
            $integral_data['integral'] = $integral;
            $integral_data['type'] = 201;  //201：后台增加/减少积分'
            $integral_data['original_integral'] = $userDetails['use_integral']; //原来积分（可使用）
            $integral_data['now_integral'] = $use_integral; //现在积分（可使用）
            $integral_data['integral_type'] = 2; // 2:增加使用积分
            $integral_data['remark'] = '管理增加可使用积分：' . $integral;
            $integral_data['aid'] = $aid;
            if ($remark){
                $integral_data['remark'] = '管理增加可使用积分：' . $integral.'管理员说明：'.$remark;
            }
            $res = $UserIntegralLogModel->addInfo($integral_data);
            if (!$res) {
                $UserDetailsModel->rollbackTrans();//事务回滚
                return JsonUtils::fail('添加用户积分记录失败', PARAM_IS_INVALID);
            }
            $UserDetailsModel->commitTrans(); //事务提交
            return JsonUtils::successful('操作成功');
        } catch (\Exception $e) {
            $UserDetailsModel->rollbackTrans();//事务回滚
            return JsonUtils::fail('添加用户积分失败', PARAM_IS_INVALID);

        }

    }

    /**
     * 后台增加佣金
     * User: hao
     * Date: 2020/8/15
     */
    public function add_commission($id, $commission,$aid,$remark='')
    {
        $UserDetailsModel = new UserDetailsModel();
        $UserMoneyLogModel = new UserMoneyLogModel();
        $filed = 'sum_money,commission_money,commission_use_money,commission_frozen_money';
        $userDetails = $UserDetailsModel->findInfo(['uid' => $id], $filed);

        try {
            $UserDetailsModel->beginTrans(); //开始事务
            $sum_money = $userDetails['sum_money'] + $commission;  //总收益金额（充值、佣金、分红）
            $commission_money = $userDetails['commission_money'] + $commission;  //获取总佣金金额（完成订单、用户增加）
            $commission_use_money = $userDetails['commission_use_money'] + $commission;  //可使用佣金（可提现）
            //修改佣金
            $res = $UserDetailsModel->updateInfo(['uid' => $id], ['sum_money' => $sum_money, 'commission_money' => $commission_money, 'commission_use_money' => $commission_use_money]);
            if (!$res) {
                $UserDetailsModel->rollbackTrans();//事务回滚
                return JsonUtils::fail('添加用户佣金失败', PARAM_IS_INVALID);
            }

            //添加记录
            $commission_data = array();
            $commission_data['uid'] = $id;
            $commission_data['pid'] = 0;
            $commission_data['money'] = $commission;
            $commission_data['commission_status'] = 1;
            $commission_data['type'] = 101;
            $commission_data['remark'] = '管理员添加佣金：' . $commission;
            $commission_data['aid'] = $aid;

            if ($remark){
                $commission_data['remark'] = '管理员添加佣金：' . $commission.',管理员说明'.$remark;

            }
            $commission_data['original_money'] = $userDetails['commission_use_money'];
            $commission_data['now_money'] = $commission_use_money;
            $commission_data['original_frozen_money'] = $userDetails['commission_frozen_money'];
            $commission_data['now_frozen_money'] = $userDetails['commission_frozen_money'];
            $res = (new UserCommissionLogModel())->addInfo($commission_data);

            if (!$res) {
                $UserDetailsModel->rollbackTrans();//事务回滚
                return JsonUtils::fail('添加用户佣金失败', PARAM_IS_INVALID);
            }

            $UserDetailsModel->commitTrans(); //事务提交
            return JsonUtils::successful('操作成功');


        } catch (\Exception $e) {
            $UserDetailsModel->rollbackTrans();//事务回滚
            return JsonUtils::fail('添加用户佣金失败1', PARAM_IS_INVALID);
        }

    }

    /**
     * 后台增加充值金额
     * User: hao
     * Date: 2020/8/15
     */
    public function add_recharge($id, $recharge,$aid,$remark)
    {

        $UserDetailsModel = new UserDetailsModel();
        $filed = 'sum_money,use_money,give_money';
        $userDetails = $UserDetailsModel->findInfo(['uid' => $id], $filed);
        try {
            $UserDetailsModel->beginTrans(); //开始事务
            $sum_money = $userDetails['sum_money'] + $recharge;  //总收益金额（充值、佣金、分红）
            $use_money = $userDetails['use_money'] + $recharge;  //可使用金额
            $give_money = $userDetails['give_money'] + $recharge;  //赠送金额总数
            //修改充值
            $res = $UserDetailsModel->updateInfo(['uid' => $id], ['sum_money' => $sum_money, 'use_money' => $use_money, 'give_money' => $give_money]);
            if (!$res) {
                $UserDetailsModel->rollbackTrans();//事务回滚
                return JsonUtils::fail('添加用户充值金额失败', PARAM_IS_INVALID);
            }

            //添加充值记录
            $datar = array();
            $datar['uid'] = $id;
            $datar['money'] = 0;
            $datar['give_money'] = $recharge;
            $datar['total_money'] = $recharge;
            $datar['status'] = 1;
            $datar['type'] = 2;
            $datar['used_is'] = 2;
            $datar['not_used_money'] = $recharge;
            $datar['original_money'] = $userDetails['use_money'];
            $datar['now_money'] = $use_money;
            $datar['remark'] = $remark;
            $datar['aid'] = $aid;
            $rid = (new UserRechargeLogModel())->addInfoId($datar);
            if (!$rid) {
                $UserDetailsModel->rollbackTrans();//事务回滚
                return JsonUtils::fail('添加用户充值金额失败', PARAM_IS_INVALID);
            }

            //增加用户使用充值表 (充值使用记录表)
            $data_use = array();
            $data_use['order_sn'] = 0;
            $data_use['uid'] = $id;
            $data_use['money'] = $recharge;
            $data_use['type'] = 1;  //1、充值金额（冲值+赠送）
            $data_use['identity_id'] = 0;
            $data_use['remarks'] = '管理员后台用户充值金额';
            $data_use['recharge_id'] = $rid;
            $data_use['original_money'] = $userDetails['use_money'];  //原来金额
            $data_use['now_money'] = $use_money; //现在金额
            $res = (new UserRechargeUseLogModel())->addInfo($data_use);
            if (!$res) {
                $UserDetailsModel->rollbackTrans();//事务回滚
                return JsonUtils::fail('添加用户充值记录失败', PARAM_IS_INVALID);
            }

            $res = (new UserBalanceLogModel())->addBalance($id, 4, $recharge, $userDetails['use_money'], $use_money);

            if (!$res) {
                $UserDetailsModel->rollbackTrans();//事务回滚
                return JsonUtils::fail('添加用户充值记录失败', PARAM_IS_INVALID);
            }

            $UserDetailsModel->commitTrans(); //事务提交
            return JsonUtils::successful('操作成功');

        } catch (\Exception $e) {
            $UserDetailsModel->rollbackTrans();//事务回滚
            return JsonUtils::fail('添加用户充值金额失败1', PARAM_IS_INVALID);
        }
    }


    /**
     * 金额列表
     * User: hao
     * Date: 2020/8/15
     */
    public function getMoney($receive)
    {
        $UserMoneyLogModel = new UserMoneyLogModel();
        $where = array();
        if (isset($receive['uid'])) {
            $where[] = ['uid', '=', $receive['uid']];
        }

        if (isset($receive['type'])) {
            $where[] = ['type', 'in', $receive['type']];
        }

        if (isset($receive['money_type'])) {
            $where[] = ['money_type', 'in', $receive['money_type']];
        }

        $arr_receive = array();
        $arr_receive['page'] = $receive['page'];
        $arr_receive['list_row'] = $receive['list_row'];
        $arr_receive['where'] = $where;
        $lists = $UserMoneyLogModel->getAllMoney($arr_receive);
        $total = $UserMoneyLogModel->statInfo($where);
        $page_total = ceil($total / $receive['list_row']);
        $res = ['total' => $total, 'page' => $receive['page'], 'page_total' => $page_total, 'list' => $lists];
        return JsonUtils::successful('操作成功', $res);
    }

    /**
     * 后台修改用户
     * User: hao
     * Date: 2020/8/15
     */
    public function modify($receive)
    {
        $UserModel = new UserModel();
        $data = array();

        try {
            $userlist = $UserModel->findInfo(['id' => $receive['id']], 'share_id,grade_id,status');

            $UserModel->beginTrans();

            //修改分享人
            if (isset($receive['pid']) && $receive['pid']) {
                $res = $UserModel->findInfo(['id' => $receive['pid'], 'share_id' => $receive['id']]);
                if ($res) {
                    $UserModel->rollbackTrans();
                    return JsonUtils::fail('不能我的下级是我的上级');
                }
                if ($receive['pid'] != $userlist['share_id']) {
                    $data['share_id'] = $receive['pid'];

                    //统计自己直推人数
                    $count_num = $UserModel->where(['share_id' => $receive['id']])->count();
                    $count_num = $count_num + 1;
                    //修改原来两级的分享人数 减少
                    if ($userlist['share_id']) {

                        //把原来上级和上上级修改团队人数
                        $res = $UserModel->setDataDec(['id' => $userlist['share_id']], 'team_num', $count_num);
                        if (!$res) {
                            $UserModel->rollbackTrans();
                            return JsonUtils::fail('操作失败');
                        }
                        $o_p_pid = $UserModel->getValues(['id' => $userlist['share_id']], 'share_id');
                        if ($o_p_pid) {
                            $res = $UserModel->setDataDec(['id' => $o_p_pid], 'team_num', 1);
                            if (!$res) {
                                $UserModel->rollbackTrans();
                                return JsonUtils::fail('操作失败');
                            }
                        }
                    }

                    //修改现在两级的分享人数
                    //把现在上级和上上级修改团队人数 增加
                    $res = $UserModel->setDataInc(['id' => $receive['pid']], 'team_num', $count_num);
                    if (!$res) {
                        $UserModel->rollbackTrans();
                        return JsonUtils::fail('操作失败');
                    }
                    $n_p_pid = $UserModel->getValues(['id' => $receive['pid']], 'share_id');
                    if ($n_p_pid) {
                        $res = $UserModel->setDataInc(['id' => $n_p_pid], 'team_num', 1);
                        if (!$res) {
                            $UserModel->rollbackTrans();
                            return JsonUtils::fail('操作失败');
                        }
                    }

                    //记录修改分享人
                    $data_arr = array();
                    $data_arr['uid'] = $receive['id'];
                    $data_arr['pid'] = $receive['pid']; //修改上级id
                    $data_arr['aid'] = $receive['aid']; //管理员id
                    $data_arr['opid'] = $userlist['share_id']; //之前上级id
                    $shareLog = new UserEditShareLogModel();
                    $rs = $shareLog->addInfo($data_arr);
                    if (!$rs) {
                        $UserModel->rollbackTrans();
                        return JsonUtils::fail('操作失败');
                    }

                }
            }

            //修改等级
            if (isset($receive['grade_id']) && $receive['grade_id']) {
                if ($receive['grade_id'] != $userlist['grade_id']) {
                    $data['grade_id'] = $receive['grade_id'];
                    //记录修改等级
                    $data_arr = array();
                    $UserUpgradeLogModel = new UserUpgradeLogModel();
                    $data_arr['uid'] = $receive['id'];
                    $data_arr['grade_id'] = $receive['grade_id'];
                    $data_arr['type'] = 2; //2：后台手动升级
                    $data_arr['remarks'] = '管理员ID：' . $receive['aid'] . ' 手动升级';//备注（以什么条件升级说明）
                    $rs = $UserUpgradeLogModel->addInfo($data_arr);
                    if (!$rs) {
                        $UserModel->rollbackTrans();
                        return JsonUtils::fail('操作失败');
                    }
                }
            }

            //修改状态
            if (isset($receive['status']) && $receive['status']) {
                $data['status'] = $receive['status'];
                if ($receive['status'] == 2) {
                    $data['disable_time'] = time();
                }
            }
            $rs = $UserModel->updateInfo(['id' => $receive['id']], $data);
            if ($rs) {
                $UserModel->commitTrans();
                return JsonUtils::successful('操作成功');
            } else {
                return JsonUtils::fail('操作失败');
            }
        } catch (\Exception $e) {
            return JsonUtils::fail('操作失败');
        }

    }


    /**
     * 提现审核
     * User: hao
     * Date: 2020/8/31
     */
    public function cash_examine($receive)
    {

        $model = new UserCashModel();
        $list = $model->findInfo(['id' => $receive['id']]);
        if ($list['examine_is'] != 3) {
            return JsonUtils::fail('已操作，请问重复操作');
        }
        Db::startTrans();

        if ($receive['examine_is'] == 2) {
            //失败回退
            try {
                if ($list['cash_mode'] == 1) {
                    //回退充值金额

                    //回退用户详情
                    $use_money = (new UserDetailsModel())->getValues(['uid' => $list['uid']], 'use_money'); //现在金额

                    $res = (new UserDetailsModel())->setDataInc(['uid' => $list['uid']], 'use_money', $list['money']);
                    if (!$res) {
                        Db::rollback();
                        return JsonUtils::fail('操作失败，增加用户详情金额有误');
                    }

                    //修改可提现记录
                    $res = (new UserRechargeCashModel())->setDataInc(['uid' => $list['uid']], 'money', $list['money']);
                    if (!$res) {
                        Db::rollback();
                        return JsonUtils::fail('操作失败，修改可提现记录有误');
                    }

                    //余额记录
                    $now_money = $use_money + $list['money'];
                    $res = (new UserBalanceLogModel())->addBalance($list['uid'], '6', $list['money'], $use_money, $now_money, $list['order_sn']);
                    if (!$res) {
                        Db::rollback();
                        return JsonUtils::fail('操作失败，余额记录有误');
                    }
                    //把金额转充值表
                    $res = $this->use_recharge($receive['id'], $use_money);

                    if (!$res) {
                        Db::rollback();
                        return JsonUtils::fail('操作失败，把金额转充值表有误');
                    }

                } else {
                    //回退佣金

                    //扣除用户详情金额
                    $commission_money = (new UserDetailsModel())->findInfo(['uid' => $list['uid']], 'commission_use_money,commission_frozen_money'); //现在金额
                    $res = (new UserDetailsModel())->setDataInc(['uid' => $list['uid']], 'commission_use_money', $list['money']);
                    if (!$res) {
                        Db::rollback();
                        return JsonUtils::fail('操作失败，扣款用户佣金金额有误');
                    }

                    $commission_data = array();
                    $commission_data['uid'] = $list['uid'];
                    $commission_data['money'] = $list['money'];
                    $commission_data['order_sn'] = $list['order_sn'];
                    $commission_data['commission_status'] = 1;
                    $commission_data['type'] = 104;
                    $commission_data['remark'] = '用户佣金提现失败，退回金额：' . $list['money'];
                    $commission_data['original_money'] = $commission_money['commission_use_money'];
                    $commission_data['now_money'] = $commission_money['commission_use_money'] + $list['money'];
                    $commission_data['original_frozen_money'] = $commission_money['commission_frozen_money'];
                    $commission_data['now_frozen_money'] = $commission_money['commission_frozen_money'];
                    $res = (new UserCommissionLogModel())->addInfo($commission_data);
                    if (!$res) {
                        Db::rollback();
                        return JsonUtils::fail('操作失败,写入佣金记录表失败');
                    }
                }


            } catch (\Exception $e) {
                Db::rollback();
                return JsonUtils::fail('操作失败，服务器异常');

            }


        }
        $rs = $model->updateInfo(['id' => $receive['id']], $receive);
        if (!$rs) {
            return JsonUtils::fail('操作有误');
        }

        Db::commit();
        return JsonUtils::successful('操作成功');

    }

    //扣除充值表的可提现金额
    public function use_recharge($id, $use_money = 0)
    {
        $where = array();
        $where[] = ['cash_id', '=', $id];
        $where[] = ['money', '<', '0'];
        $list = (new UserRechargeUseLogModel())->getList($where, 'money,recharge_id,uid,identity_id,order_sn');

        //找不到记录
        if (!$list) {
            return false;
        }

        //循环退回给充值表
        foreach ($list as $key => $value) {
            $money = abs($value['money']);
            $userRecharge = (new UserRechargeLogModel())->findInfo(['id' => $value['recharge_id']], 'id,qte_money,not_used_money');
            $qte_money = $userRecharge['qte_money'] + $money;  //可提金额
            $not_used_money = $userRecharge['not_used_money'] + $money; //可消费金额
            $res = (new UserRechargeLogModel())->updateInfo(['id' => $userRecharge['id']], ['qte_money' => $qte_money, 'not_used_money' => $not_used_money]);

            if (!$res) {
                return false;
            }
            //加入充值使用数据 (充值使用记录表)
            $data_use = array();
            $data_use['order_sn'] = $value['order_sn'];
            $data_use['uid'] = $value['uid'];
            $data_use['money'] = $money;
            $data_use['type'] = 5;
            $data_use['identity_id'] = $value['identity_id'];
            $data_use['remarks'] = '管理员提现审核失败';
            $data_use['recharge_id'] = $userRecharge['id'];
            $data_use['original_money'] = $use_money;
            $data_use['now_money'] = $use_money + $money;
            $data_use['cash_id'] = $id;
            $res = (new UserRechargeUseLogModel())->addInfo($data_use);

            if (!$res) {
                return false;
            }
            $use_money = $use_money + $money;
        }
        return true;
    }


}