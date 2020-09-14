<?php


namespace app\common\logic\user;


use app\apiadmin\controller\user\UserRechargeLog;
use app\common\model\config\WebsiteConfigModel;
use app\common\model\settlement\AdminRechargeUseLogModel;
use app\common\model\settlement\SettlementModel;
use app\common\model\user\UserBalanceLogModel;
use app\common\model\user\UserBalanceOrderLogModel;
use app\common\model\user\UserCommissionLogModel;
use app\common\model\user\UserDetailsModel;
use app\common\model\user\UserGradeModel;
use app\common\model\user\UserModel;
use app\common\model\user\UserRechargeCashModel;
use app\common\model\user\UserRechargeLogModel;
use app\common\model\user\UserRechargeUseLogModel;
use app\common\model\user\UserUpgradeLogModel;

//调用方法
class UserMethodLogic
{
    protected $userModel;
    protected $userGradeModel;

    //公共
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userGradeModel = new UserGradeModel();
    }


    /**
     * 商品用户等级优惠
     * User: hao
     * Date: 2020/8/15
     * $uid 用户id
     * $money 金额
     * return 优惠之后价格
     *
     */
    public function goods_money($param = [])
    {
        $uid = $param['uid'];
        $money = $param['money'];
        //等级
        $grade_id = $this->userModel->getValues(['id' => $uid], 'grade_id');
        //优惠价
        $discount = $this->userGradeModel->getValues(['id' => $grade_id], 'discount');
        $money = $money * $discount / 100;
        return sprintf("%.2f", $money);
    }


    /**
     * 使用金额支付订单
     * User: hao
     * Date: 2020/8/26
     * $uid 用户id
     * $money 金额
     * $order_sn 订单  (子订单)
     * return true / false
     * **调用之前写好开始事务，返回false 即 回滚 、返回ture即提交 **
     */
    public function order_balance($param = [])
    {
        $order_sn = $param['order_sn'];
        $money = $param['money'];
        $uid = $param['uid'];
        //防止重复扣钱
        $res = (new UserBalanceOrderLogModel())->findInfo(['uid' => $uid, 'order_sn' => $order_sn, 'type' => 1]);
        if ($res) {
            return ['code' => false, 'msg' => '订单已扣钱'];
        }
        $res = (new UserBalanceOrderLogModel())->addInfo(['uid' => $uid, 'order_sn' => $order_sn, 'type' => 1, 'money' => $money]);
        if (!$res) {
            return ['code' => false, 'msg' => '添加订单记录表失败'];
        }


        //扣除用户详情余额
        $use_money = (new UserDetailsModel())->getValues(['uid' => $uid], 'use_money');//可用充值余额
        if ($use_money < $money) {
            return ['code' => false, 'msg' => '余额不足'];
        }

        $res = (new UserDetailsModel())->setDataDec(['uid' => $uid], 'use_money', $money);
        if ($res === false) {
            return ['code' => false, 'msg' => '扣除用户余额失败'];
        }

        //用户余额记录
        $now_money = $use_money - $money;
        $res = (new UserBalanceLogModel())->addBalance($uid, 3, $money, $use_money, $now_money, $order_sn);
        if ($res === false) {
            return ['code' => false, 'msg' => '记录余额明细失败'];
        }

        //把充值表全部改为已使用
        $res = (new UserRechargeLogModel())->updateInfo(['uid' => $uid], ['used_is' => 2]);
        if ($res === false) {
            return ['code' => false, 'msg' => '改变充值表失败'];
        }

        //用户充值可提现表改为金额0
        $res = (new UserRechargeCashModel())->where(['uid' => $uid])->update(['money' => 0, 'time' => time()]);
        if ($res === false) {
            return ['code' => false, 'msg' => '改变用户充值可提现表失败'];
        }

        //扣除充值表
        $res = $this->use_recharge($order_sn, $money, $uid, $use_money);
        if ($res['code'] === false) {
            return ['code' => false, 'msg' => $res['msg']];
        }
        return ['code' => true, 'msg' => '操作成功'];
    }


    //扣除充值表 递归方法
    public function use_recharge($order_sn, $money, $uid, $use_money)
    {
        $recharge_where = array();
        $recharge_where[] = ['uid', '=', $uid];
        $recharge_where[] = ['status', '=', 1];
        $recharge_where[] = ['not_used_money', '>', 0];
        $list = (new UserRechargeLogModel())->where($recharge_where)->order('create_time asc')->find();

        if (!$list) {
            return ['code' => false, 'msg' => '获取用户充值记录失败'];
        }

        //剩下的钱
        $over_money = $money - $list['not_used_money'];


        if ($over_money > 0) {
            $not_used_money = 0;//剩下的金额
            $deduction_money = $list['not_used_money'];//扣除金额
        } else {
            $not_used_money = $list['not_used_money'] - $money;//剩下的金额
            $deduction_money = $money;
        }

        //充值记录钱扣除
        $res = (new UserRechargeLogModel())->updateInfo(['id' => $list['id']], ['not_used_money' => $not_used_money]);
        if (!$res) {
            return ['code' => false, 'msg' => '充值记录钱扣除失败'];
        }

        //门店待返金额 %
//        $commission_scale = (new WebsiteConfigModel())->getValues(['type' => 'recharge_commission_scale'], 'val');
        $commission_scale = $list['recharge_commission_scale'];

        //使用订单记录(充值使用记录表)
        $data_use = array();
        $data_use['order_sn'] = $order_sn;
        $data_use['uid'] = $uid;
        $data_use['money'] = '-' . $deduction_money;
        $data_use['type'] = 3;
        $data_use['identity_id'] = $list['identity_id'];
        $data_use['remarks'] = '使用支付订单：' . $order_sn;
        $data_use['recharge_id'] = $list['id'];
        $data_use['original_money'] = $use_money;
        $data_use['now_money'] = $use_money - $deduction_money;
        $data_use['refund_money'] = $deduction_money;
        $data_use['recharge_commission_scale'] = $commission_scale;
        $res = (new UserRechargeUseLogModel())->addInfo($data_use);
        if (!$res) {
            return ['code' => false, 'msg' => '记录充值使用记录表失败'];
        }

        $commission_money = $deduction_money * $commission_scale / 100;


        $original_money = 0;  //原来可提金额
        $now_money = 0;  //现在可提金额
        $frozen_original_money = 0;  //原来冻结金额
        $frozen_now_money = 0;  //现在冻结金额
        if ($list['identity_id'] != 0) {
            //修改门店冻结金额
            $settlement = (new SettlementModel())->findInfo(['admin_id' => $list['identity_id']], 'wallet,wallet_frozen,wallet_total');
            $wallet_frozen = $settlement['wallet_frozen'] + $commission_money;
            $wallet_total = $settlement['wallet_total'] + $commission_money;
            $res = (new SettlementModel())->updateInfo(['admin_id' => $list['identity_id']], ['wallet_frozen' => $wallet_frozen, 'wallet_total' => $wallet_total]);
            $original_money = $settlement['wallet'];
            $frozen_original_money = $settlement['wallet_frozen'];
            $now_money = $settlement['wallet'];
            $frozen_now_money = $wallet_frozen;
            if (!$res) {
                return ['code' => false, 'msg' => '修改门店冻结金额失败'];
            }
        }

        //门店待返金额记录
        $admin_data = array();
        $admin_data['order_sn'] = $order_sn;
        $admin_data['uid'] = $uid;
        $admin_data['money'] = $commission_money;//返利金额
        $admin_data['type'] = 1;
        $admin_data['identity_id'] = $list['identity_id'];
        $admin_data['remarks'] = '使用充值金额消费' . $order_sn . '订单';
        $admin_data['original_money'] = $original_money;
        $admin_data['now_money'] = $now_money;
        $admin_data['frozen_original_money'] = $frozen_original_money;
        $admin_data['frozen_now_money'] = $frozen_now_money;
        $admin_data['recharge_id'] = $list['id'];

        $res = (new AdminRechargeUseLogModel())->addInfoId($admin_data);
        if (!$res) {
            return ['code' => false, 'msg' => '门店待返金额记录失败'];
        }

        if ($over_money > 0) {
            //重新调起
            return $this->use_recharge($order_sn, $over_money, $uid, $data_use['now_money']);
        }
        return ['code' => true, 'msg' => '操作成功'];
    }


    /**
     * 使用金额支付订单退款
     * User: hao
     * Date: 2020/8/26
     * $uid 用户id
     * $money 退款金额
     * $order_sn 下单订单id  (子订单)
     * $goods_id 商品id
     *return true / false
     * **调用之前写好开始事务，返回false 即 回滚 、返回true即提交 **
     */
    public function refund_balance($param = [])
    {
        $order_sn = $param['order_sn'];
        $money = $param['money'];
        $uid = $param['uid'];
        $goods_id = $param['goods_id'] ?? 0;
        //防止重复扣钱
        $res = (new UserBalanceOrderLogModel())->findInfo(['uid' => $uid, 'order_sn' => $order_sn, 'type' => 2, 'goods_id' => $goods_id]);
        if ($res) {
            return ['code' => false, 'msg' => '订单已退款'];

        }

        $res = (new UserBalanceOrderLogModel())->addInfo(['uid' => $uid, 'order_sn' => $order_sn, 'type' => 2, 'money' => $money, 'goods_id' => $goods_id]);
        if (!$res) {
            return ['code' => false, 'msg' => '订单记录表失败'];
        }

        //金额退回用户详情表
        $use_money = (new UserDetailsModel())->getValues(['uid' => $uid], 'use_money');//可用充值余额

        $res = (new UserDetailsModel())->setDataInc(['uid' => $uid], 'use_money', $money);
        if (!$res) {
            return ['code' => false, 'msg' => '用户退回余额失败'];
        }

        //用户余额记录
        $now_money = $use_money + $money;
        $res = (new UserBalanceLogModel())->addBalance($uid, 5, $money, $use_money, $now_money, $order_sn);
        if ($res === false) {
            return ['code' => false, 'msg' => '用户余额记录失败'];
        }

        //通过递归查询 (充值使用记录表) 返回金额充值表 ，+充值使用记录表返回金额，扣除门店冻结金额，总金额，记录门店扣除表
        $res = $this->refund_recharge($order_sn, $money, $uid, $use_money);
        if ($res['code'] === false) {
            return ['code' => false, 'msg' => $res['msg']];
        }
        return ['code' => true, 'msg' => '操作成功'];
    }

    //回退金额 递归方法
    public function refund_recharge($order_sn, $money, $uid, $use_money)
    {
        $use_where = array();
        $use_where[] = ['order_sn', '=', $order_sn];
        $use_where[] = ['uid', '=', $uid];
        $use_where[] = ['type', '=', '3'];
        $use_where[] = ['refund_money', '>', 0];
        $list = (new UserRechargeUseLogModel())->where($use_where)->order('create_time asc')->find();
        if (!$list) {
            return ['code' => false, 'msg' => '获取用户充值使用记录表失败'];
        }

        $over_money = $money - $list['refund_money'];

        //减不完，递归
        if ($over_money > 0) {
            $refund_money = 0;//修改可退金额
            $recharge_money = $list['refund_money'];  //金额返回充值列表
        } else {
            $refund_money = $list['refund_money'] - $money; //修改可退金额
            $recharge_money = $money;  //金额返回充值列表
        }
        //改变可退金额
        $res = (new UserRechargeUseLogModel())->updateInfo(['id' => $list['id']], ['refund_money' => $refund_money]);
        if (!$res) {
            return ['code' => false, 'msg' => '改变可退金额失败'];
        }

        //返回充值表
        $res = (new UserRechargeLogModel())->setDataInc(['id' => $list['recharge_id']], 'not_used_money', $recharge_money);
        if (!$res) {
            return ['code' => false, 'msg' => '金额返回充值表失败'];
        }

        //记录返回金额(充值使用记录表)
        $use_data = array();
        $use_data['order_sn'] = $order_sn;
        $use_data['uid'] = $uid;
        $use_data['money'] = $recharge_money;
        $use_data['type'] = 4;  //4 退回金额
        $use_data['identity_id'] = $list['identity_id'];
        $use_data['remarks'] = '订单号：' . $order_sn . ' 退款金额' . $use_money . '元,返回充值表';
        $use_data['recharge_id'] = $list['recharge_id'];
        $use_data['original_money'] = $use_money;
        $use_data['now_money'] = $use_money - $recharge_money;
        $res = (new UserRechargeUseLogModel())->addInfoId($use_data);
        if (!$res) {
            return ['code' => false, 'msg' => '添加充值使用记录表失败'];
        }

        //退回门店冻结金额
        $commission_money = $recharge_money * $list['recharge_commission_scale'] / 100;

        $original_money = 0;  //原来可提金额
        $now_money = 0;  //现在可提金额
        $frozen_original_money = 0;  //原来冻结金额
        $frozen_now_money = 0;  //现在冻结金额
        if ($list['identity_id'] != 0) {
            //修改门店冻结金额
            $settlement = (new SettlementModel())->findInfo(['admin_id' => $list['identity_id']], 'wallet,wallet_frozen,wallet_total');
            $wallet_frozen = $settlement['wallet_frozen'] - $commission_money;
            $wallet_total = $settlement['wallet_total'] - $commission_money;
            $res = (new SettlementModel())->updateInfo(['admin_id' => $list['identity_id']], ['wallet_frozen' => $wallet_frozen, 'wallet_total' => $wallet_total]);
            if (!$res) {
                return ['code' => false, 'msg' => '修改门店冻结金额失败'];
            }
            $original_money = $settlement['wallet'];
            $frozen_original_money = $settlement['wallet_frozen'];
            $now_money = $settlement['wallet'];
            $frozen_now_money = $wallet_frozen;
        }


        //门店待返金额记录
        $admin_data = array();
        $admin_data['order_sn'] = $order_sn;
        $admin_data['uid'] = $uid;
        $admin_data['money'] = '-' . $commission_money;//返利金额
        $admin_data['type'] = 2;
        $admin_data['identity_id'] = $list['identity_id'];
        $admin_data['remarks'] = '退款：' . $order_sn . '订单金额 ' . $recharge_money . '元' . ',扣除冻结' . $commission_money . '元';
        $admin_data['original_money'] = $original_money;
        $admin_data['now_money'] = $now_money;
        $admin_data['frozen_original_money'] = $frozen_original_money;
        $admin_data['frozen_now_money'] = $frozen_now_money;
        $admin_data['recharge_id'] = $list['id'];
        $res = (new AdminRechargeUseLogModel())->addInfoId($admin_data);
        if (!$res) {
            return ['code' => false, 'msg' => '添加门店待返金额记录失败'];
        }

        if ($over_money > 0) {
            return $this->refund_recharge($order_sn, $over_money, $uid, $use_data['now_money']);
        }
        return ['code' => true, 'msg' => '操作成功'];
    }


    /**
     * 订单完成 （不会退款）
     * User: hao
     * Date: 2020/8/26
     * $uid 用户id
     * $money 订单金额
     * $order_sn 订单id (子订单)
     * $is 1:是金额付款  0：不是金额付款
     *return true / false
     * **调用之前写好开始事务，返回false 即 回滚 、返回true即提交 **
     */
    public function order_completion($param = [])
    {

        $order_sn = $param['order_sn'];
        $money = $param['money'];
        $uid = $param['uid'];
        $is = $param['is'] ?? 0;

        //防止重复扣钱
        $res = (new UserBalanceOrderLogModel())->findInfo(['uid' => $uid, 'order_sn' => $order_sn, 'type' => 3]);
        if ($res) {
            return ['code' => false, 'msg' => '订单已操作'];
        }

        $res = (new UserBalanceOrderLogModel())->addInfo(['uid' => $uid, 'order_sn' => $order_sn, 'type' => 3, 'money' => $money]);
        if (!$res) {
            return ['code' => false, 'msg' => '添加订单记录失败'];
        }

        //查看用户是否已达到可升级
        $consume_money = (new UserDetailsModel())->getValues(['uid' => $uid], 'consume_money');
        $consume_money = $consume_money + $money;
        $res = (new UserDetailsModel())->updateInfo(['uid' => $uid], ['consume_money' => $consume_money]);
        if ($res === false) {
            return ['code' => false, 'msg' => '添加用户总销售额失败'];
        }

        $UserGrade = new UserGradeModel();
        $where_grade = array();
        $where_grade[] = ['status', '=', 1];
        $where_grade[] = ['is_shareholder', '=', 0];
        $where_grade[] = ['full_money', '<=', $consume_money];

        $grade = $UserGrade->where($where_grade)->order('full_money desc')->field('id,full_money')->find();
        $grade_id = (new UserModel())->getValues(['id' => $uid], 'grade_id');
        if ($grade && $grade['id'] > $grade_id) {
            //消费总金额升级
            $res = (new UserModel())->updateInfo(['id' => $uid], ['grade_id' => $grade['id']]);
            if ($res === false) {
                return ['code' => false, 'msg' => '消费总金额升级失败'];
            }

            $upgradeMode = new UserUpgradeLogModel();
            $up_data = array();
            $up_data['uid'] = $uid;
            $up_data['grade_id'] = $grade['id'];
            $up_data['type'] = 1;
            $up_data['create_time'] = time();
            $up_data['remarks'] = '满足消费总金额:' . $grade['full_money'];
            $res = $upgradeMode->addInfo($up_data);
            if ($res === false) {
                return ['code' => false, 'msg' => '添加升级失败'];
            }
        }

        //如果金额支付
        if ($is == 1) {
            $where_recharge = array();
            $where_recharge[] = ['order_sn', '=', $order_sn];

            $list_recharge = (new AdminRechargeUseLogModel())
                ->field('sum(money) as money,identity_id,recharge_id')
                ->where($where_recharge)
                ->group('identity_id,recharge_id')
                ->select();
            foreach ($list_recharge as $key => $value) {
                $original_money = 0;  //原来可提金额
                $now_money = 0;  //现在可提金额
                $frozen_original_money = 0;  //原来冻结金额
                $frozen_now_money = 0;  //现在冻结金额

                //0平台
                if ($value['identity_id'] != 0) {
                    //减去冻结  增加可提
                    //修改门店冻结金额
                    $settlement = (new SettlementModel())->findInfo(['admin_id' => $value['identity_id']], 'wallet,wallet_frozen,wallet_total');
                    $wallet_frozen = $settlement['wallet_frozen'] - $value['money'];
                    $wallet = $settlement['wallet'] + $value['money'];
                    $res = (new SettlementModel())->updateInfo(['admin_id' => $value['identity_id']], ['wallet_frozen' => $wallet_frozen, 'wallet' => $wallet]);
                    if (!$res) {
                        return ['code' => false, 'msg' => '修改门店冻结金额失败'];
                    }
                    $original_money = $settlement['wallet'];
                    $frozen_original_money = $settlement['wallet_frozen'];
                    $now_money = $wallet;
                    $frozen_now_money = $wallet_frozen;
                }

                //写入扣除冻结 //门店待返金额记录
                $admin_data = array();
                $admin_data['order_sn'] = $order_sn;
                $admin_data['uid'] = $uid;
                $admin_data['money'] = '-' . $value['money'];//返利金额
                $admin_data['type'] = 3;
                $admin_data['identity_id'] = $value['identity_id'];
                $admin_data['remarks'] = $order_sn . '订单完成,把冻结' . $value['money'] . '元金额扣除';
                $admin_data['original_money'] = $original_money;
                $admin_data['now_money'] = $original_money;
                $admin_data['frozen_original_money'] = $frozen_original_money;
                $admin_data['frozen_now_money'] = $frozen_now_money;
                $admin_data['recharge_id'] = $value['recharge_id'];

                $res = (new AdminRechargeUseLogModel())->addInfoId($admin_data);
                if (!$res) {
                    return ['code' => false, 'msg' => '修改门店扣除冻结返利失败'];
                }

                //写入加入可提 //门店待返金额记录
                $admin_data = array();
                $admin_data['order_sn'] = $order_sn;
                $admin_data['uid'] = $uid;
                $admin_data['money'] = $value['money'];//返利金额
                $admin_data['type'] = 4;
                $admin_data['identity_id'] = $value['identity_id'];
                $admin_data['remarks'] = $order_sn . '订单完成,把冻结' . $value['money'] . '元金额转入可提金额';
                $admin_data['original_money'] = $original_money;
                $admin_data['now_money'] = $now_money;
                $admin_data['frozen_original_money'] = $frozen_original_money;
                $admin_data['frozen_now_money'] = $frozen_now_money;
                $admin_data['recharge_id'] = $value['recharge_id'];
                $res = (new AdminRechargeUseLogModel())->addInfoId($admin_data);
                if (!$res) {
                    return ['code' => false, 'msg' => '修改门店加入可提返利失败'];
                }

                //改变充值表门店返利数据
                $user_recharge = (new UserRechargeLogModel())->findInfo(['id' => $value['recharge_id']], 'stay_rebate,already_rebate');
                $stay_rebate = $user_recharge['stay_rebate'] - $value['money'];
                $already_rebate = $user_recharge['already_rebate'] + $value['money'];
                $res = (new UserRechargeLogModel())->updateInfo(['id' => $value['recharge_id']], ['stay_rebate' => $stay_rebate, 'already_rebate' => $already_rebate]);
                if (!$res) {
                    return ['code' => false, 'msg' => '改变充值门店返失败'];
                }
            }
        }
        return ['code' => true, 'msg' => '操作成功'];
    }

    /**
     * 下单获得佣金
     * User: hao
     * Date: 2020.09.03
     * $uid 购买用户id
     * $commission_one  一级佣金
     * $commission_two  二级佣金
     * $goods_id  关联商品
     * $order_sn 订单号
     *return true / false
     * **调用之前写好开始事务，返回false 即 回滚 、返回true即提交 **
     */
    public function order_commission($param = [])
    {
        $order_sn = $param['order_sn'] ?? 0;
        $commission_one = $param['commission_one'] ?? 0;
        $commission_two = $param['commission_two'] ?? 0;
        $goods_id = $param['goods_id'] ?? 0;
        $uid = $param['uid'] ?? 0;
        if (!$uid) {
            return ['code' => false, 'msg' => '参数有误'];
        }
        //防止重复进入
        $res = (new UserCommissionLogModel())->findInfo(['pid' => $uid, 'goods_id' => $goods_id, 'order_sn' => $order_sn, 'type' => 102]);
        if ($res) {
            return ['code' => false, 'msg' => '已返利，请勿重复返利'];
        }

        //购买者用户
        $goods_user = $this->userModel->findInfo(['id' => $uid], 'share_id,grade_id');

        //一级推荐
        $uid_one = $goods_user['share_id'];

        if ($uid_one) {
            $grade_one = $this->userModel->getValues(['id' => $uid_one], 'grade_id');
        }

        //二级推荐
        $uid_tow = 0;
        if ($uid_one) {
            $uid_tow = $this->userModel->getValues(['id' => $uid_one], 'share_id');
            $grade_tow = $this->userModel->getValues(['id' => $uid_tow], 'grade_id');
        }

        //一级推荐
        if ($uid_one && $commission_one > 0) {
            //改变用户详情冻结佣金
            $user_details = (new UserDetailsModel())->findInfo(['uid' => $uid_one], 'sum_money,commission_money,commission_frozen_money,commission_use_money');

            $user_data = array();
            $user_data['sum_money'] = $user_details['sum_money'] + $commission_one;    //总收益
            $user_data['commission_money'] = $user_details['commission_money'] + $commission_one;  //佣金总收益
            $user_data['commission_frozen_money'] = $user_details['commission_frozen_money'] + $commission_one;// 冻结佣金

            $res = (new UserDetailsModel())->updateInfo(['uid' => $uid_one], $user_data);
            if (!$res) {
                return ['code' => false, 'msg' => '修改一级用户佣金有误'];
            }

            //记录佣金明细
            $commission_data = array();
            $commission_data['uid'] = $uid_one;
            $commission_data['pid'] = $uid;
            $commission_data['money'] = $commission_one;
            $commission_data['goods_id'] = $goods_id;
            $commission_data['order_sn'] = $order_sn;
            $commission_data['commission_status'] = 2;
            $commission_data['type'] = 102;
            $commission_data['remark'] = 'ID：' . $uid . ' 用户下单，ID ' . $uid_one . '用户获得' . $commission_one . '元待返佣金';
            $commission_data['original_money'] = $user_details['commission_use_money'];//原来可提
            $commission_data['now_money'] = $user_details['commission_use_money'];//现在可提金额
            $commission_data['original_frozen_money'] = $user_details['commission_frozen_money'];//原来冻结金额
            $commission_data['now_frozen_money'] = $user_data['commission_frozen_money'];//现在冻结金额
            $commission_data['distribution_level'] = 1;
            $commission_data['uid_grade'] = $grade_one;
            $commission_data['pid_grade'] = $goods_user['grade_id'];
            $res = (new UserCommissionLogModel())->addInfo($commission_data);
            if (!$res) {
                return ['code' => false, 'msg' => '记录一级返利佣金有误'];
            }
        }

        //二级推荐
        if ($uid_tow && $commission_two > 0) {
            //改变用户详情冻结佣金
            $user_details = (new UserDetailsModel())->findInfo(['uid' => $uid_tow], 'sum_money,commission_money,commission_frozen_money,commission_use_money');

            $user_data = array();
            $user_data['sum_money'] = $user_details['sum_money'] + $commission_two;    //总收益
            $user_data['commission_money'] = $user_details['commission_money'] + $commission_two;  //佣金总收益
            $user_data['commission_frozen_money'] = $user_details['commission_frozen_money'] + $commission_two;// 冻结佣金

            $res = (new UserDetailsModel())->updateInfo(['uid' => $uid_tow], $user_data);
            if (!$res) {
                return ['code' => false, 'msg' => '修改二级用户佣金有误'];
            }

            //记录佣金明细
            $commission_data = array();
            $commission_data['uid'] = $uid_tow;
            $commission_data['pid'] = $uid;
            $commission_data['money'] = $commission_two;
            $commission_data['goods_id'] = $goods_id;
            $commission_data['order_sn'] = $order_sn;
            $commission_data['commission_status'] = 2;
            $commission_data['type'] = 102;
            $commission_data['remark'] = 'ID：' . $uid . ' 用户下单，ID ' . $uid_tow . '用户获得' . $commission_two . '元待返佣金';
            $commission_data['original_money'] = $user_details['commission_use_money'];//原来可提
            $commission_data['now_money'] = $user_details['commission_use_money'];//现在可提金额
            $commission_data['original_frozen_money'] = $user_details['commission_frozen_money'];//原来冻结金额
            $commission_data['now_frozen_money'] = $user_data['commission_frozen_money'];//现在冻结金额
            $commission_data['distribution_level'] = 2;
            $commission_data['uid_grade'] = $grade_tow;
            $commission_data['pid_grade'] = $goods_user['grade_id'];
            $res = (new UserCommissionLogModel())->addInfo($commission_data);
            if (!$res) {
                return ['code' => false, 'msg' => '记录二级返利佣金有误'];
            }

        }
        return ['code' => true, 'msg' => '操作成功'];


    }

    /**
     * 退款待佣金扣除
     * User: hao
     * Date: 2020.09.03
     * $uid 购买用户id
     * $goods_id  关联商品
     * $order_sn 订单号
     *return true / false
     * **调用之前写好开始事务，返回false 即 回滚 、返回true即提交 **
     */
    public function refund_commission($param = []){
        $order_sn = $param['order_sn'] ?? 0;
        $goods_id = $param['goods_id'] ?? 0;
        $uid = $param['uid'] ?? 0;
        if (!$uid) {
            return ['code' => false, 'msg' => '参数有误'];
        }

        //防止重复进入
        $res = (new UserCommissionLogModel())->findInfo(['pid' => $uid, 'goods_id' => $goods_id, 'order_sn' => $order_sn, 'type' => 201]);
        if ($res) {
            return ['code' => false, 'msg' => '已返利，请勿重复返利'];
        }



        $where_comm = array();
        $where_comm['pid'] = $uid;
        $where_comm['goods_id'] = $goods_id;
        $where_comm['order_sn'] = $order_sn;
        $where_comm['type'] = '102'; //用户下单待返佣金 ，

        $commission_list =  (new UserCommissionLogModel())->getList($where_comm,'id,money,uid,pid,goods_id,order_sn,uid_grade,distribution_level,pid_grade');

        if (!$commission_list){
            return ['code'=>true,'msg'=>'没有返利用户'];
        }

        foreach ($commission_list as $key=>$value){
            $user =  (new UserDetailsModel())->findInfo(['uid' => $value['uid']], 'sum_money,commission_money,commission_frozen_money,commission_use_money');
            $user_data = array();
            $user_data['sum_money'] = $user['sum_money'] - $value['money'];    //总收益
            $user_data['commission_money'] = $user['commission_money'] - $value['money'];  //佣金总收益
            $user_data['commission_frozen_money'] = $user['commission_frozen_money'] - $value['money'];// 冻结佣金

            $res = (new UserDetailsModel())->updateInfo(['uid' => $value['uid']], $user_data);
            if (!$res) {
                return ['code' => false, 'msg' => '修改'.$value['distribution_level'].'级用户佣金有误'];
            }

            //记录佣金明细
            $commission_data = array();
            $commission_data['uid'] = $value['uid'];
            $commission_data['pid'] = $uid;
            $commission_data['money'] = '-'.$value['money'];
            $commission_data['goods_id'] = $goods_id;
            $commission_data['order_sn'] = $order_sn;
            $commission_data['commission_status'] = 2;
            $commission_data['type'] = 201;
            $commission_data['remark'] = 'ID：' . $uid . ' 用户退款，ID ' . $value['uid'] . '用户扣除' . $value['money'] . '元待返佣金';
            $commission_data['original_money'] = $user['commission_use_money'];//原来可提
            $commission_data['now_money'] = $user['commission_use_money'];//现在可提金额
            $commission_data['original_frozen_money'] = $user['commission_frozen_money'];//原来冻结金额
            $commission_data['now_frozen_money'] = $user_data['commission_frozen_money'];//现在冻结金额
            $commission_data['distribution_level'] = $value['distribution_level'];
            $commission_data['uid_grade'] = $value['uid_grade'];
            $commission_data['pid_grade'] =  $value['pid_grade'];
            $res = (new UserCommissionLogModel())->addInfo($commission_data);
            if (!$res) {
                return ['code' => false, 'msg' => '记录'.$value['distribution_level'].'级返利佣金有误'];
            }
        }
        return ['code' => true, 'msg' => '操作成功'];
    }

    /**
     * 完成订单佣金可提
     * User: hao
     * Date: 2020.09.03
     * $uid 购买用户id
     * $goods_id  关联商品
     * $order_sn 订单号
     *return true / false
     * **调用之前写好开始事务，返回false 即 回滚 、返回true即提交 **
     */
    public function goods_order_commission($param = []){
        $order_sn = $param['order_sn'] ?? 0;
        $goods_id = $param['goods_id'] ?? 0;
        $uid = $param['uid'] ?? 0;
        if (!$uid) {
            return ['code' => false, 'msg' => '参数有误'];
        }
        //是否已退款
        $res = (new UserCommissionLogModel())->findInfo(['pid' => $uid, 'goods_id' => $goods_id, 'order_sn' => $order_sn, 'type' => 201]);
        if ($res) {
            return ['code' => false, 'msg' => '已退款,可待佣金已扣除'];
        }

        //防止重复进入
        $res = (new UserCommissionLogModel())->findInfo(['pid' => $uid, 'goods_id' => $goods_id, 'order_sn' => $order_sn, 'type' => 103]);
        if ($res) {
            return ['code' => false, 'msg' => '已返利，请勿重复返利'];
        }

        $where_comm = array();
        $where_comm['pid'] = $uid;
        $where_comm['goods_id'] = $goods_id;
        $where_comm['order_sn'] = $order_sn;

        $commission_list = (new UserCommissionLogModel())
            ->field('sum(money) as money,uid,uid_grade,distribution_level,pid_grade')
            ->where($where_comm)
            ->group('uid,pid,goods_id,order_sn,uid_grade,distribution_level,pid_grade')
            ->select();

        if (!$commission_list){
            return ['code'=>true,'msg'=>'没有返利用户'];
        }

        foreach ($commission_list as $key=>$value){
            $user =  (new UserDetailsModel())->findInfo(['uid' => $value['uid']], 'sum_money,commission_money,commission_frozen_money,commission_use_money');
            $user_data = array();
            $user_data['commission_frozen_money'] = $user['commission_frozen_money'] - $value['money'];// 冻结佣金
            $user_data['commission_use_money'] = $user['commission_use_money'] + $value['money'];// 可用佣金

            $res = (new UserDetailsModel())->updateInfo(['uid' => $value['uid']], $user_data);
            if (!$res) {
                return ['code' => false, 'msg' => '修改'.$value['distribution_level'].'级用户佣金有误'];
            }


            //记录扣除待佣金明细
            $commission_data = array();
            $commission_data['uid'] = $value['uid'];
            $commission_data['pid'] = $uid;
            $commission_data['money'] = '-'.$value['money'];
            $commission_data['goods_id'] = $goods_id;
            $commission_data['order_sn'] = $order_sn;
            $commission_data['commission_status'] = 2;
            $commission_data['type'] = 202;
            $commission_data['remark'] = 'ID：' . $uid . ' 用户完成订单，ID ' . $value['uid'] . '用户扣除' . $value['money'] . '元待返佣金';
            $commission_data['original_money'] = $user['commission_use_money'];//原来可提
            $commission_data['now_money'] = $user['commission_use_money'];//现在可提金额
            $commission_data['original_frozen_money'] = $user['commission_frozen_money'];//原来冻结金额
            $commission_data['now_frozen_money'] = $user_data['commission_frozen_money'];//现在冻结金额
            $commission_data['distribution_level'] = $value['distribution_level'];
            $commission_data['uid_grade'] = $value['uid_grade'];
            $commission_data['pid_grade'] =  $value['pid_grade'];
            $res = (new UserCommissionLogModel())->addInfo($commission_data);
            if (!$res) {
                return ['code' => false, 'msg' => '记录'.$value['distribution_level'].'级返利佣金有误'];
            }

            //记录增加可用佣金明细
            $commission_data = array();
            $commission_data['uid'] = $value['uid'];
            $commission_data['pid'] = $uid;
            $commission_data['money'] = $value['money'];
            $commission_data['goods_id'] = $goods_id;
            $commission_data['order_sn'] = $order_sn;
            $commission_data['commission_status'] = 1;
            $commission_data['type'] = 103;
            $commission_data['remark'] = 'ID：' . $uid . ' 用户完成订单，ID ' . $value['uid'] . '用户增加' . $value['money'] . '元可用佣金';
            $commission_data['original_money'] = $user['commission_use_money'];//原来可提
            $commission_data['now_money'] = $user_data['commission_use_money'];//现在可提金额
            $commission_data['original_frozen_money'] = $user_data['commission_frozen_money'];//原来冻结金额
            $commission_data['now_frozen_money'] = $user_data['commission_frozen_money'];//现在冻结金额
            $commission_data['distribution_level'] = $value['distribution_level'];
            $commission_data['uid_grade'] = $value['uid_grade'];
            $commission_data['pid_grade'] =  $value['pid_grade'];
            $res = (new UserCommissionLogModel())->addInfo($commission_data);
            if (!$res) {
                return ['code' => false, 'msg' => '记录'.$value['distribution_level'].'级返利佣金有误'];
            }

        }

        return ['code' => true, 'msg' => '操作成功'];


    }




}