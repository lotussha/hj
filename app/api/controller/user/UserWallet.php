<?php


namespace app\api\controller\user;


use app\api\controller\Api;
use app\common\logic\user\UserWalletLogic;
use app\common\model\config\WebsiteConfigModel;
use app\common\model\user\UserBalanceLogModel;
use app\common\model\user\UserCashModel;
use app\common\model\user\UserCommissionLogModel;
use app\common\model\user\UserDetailsModel;
use app\common\model\user\UserIntegralLogModel;
use app\common\model\user\UserRechargeCashModel;
use app\common\model\user\UserRechargeLogModel;
use app\common\validate\user\UserApiValidate;
use sakuno\utils\JsonUtils;
use think\App;
use think\Request;

//用户钱包
class UserWallet extends Api
{
    protected $walletLogic;

    public function __construct(Request $request, App $app)
    {
        parent::__construct($request, $app);
        $this->walletLogic = new UserWalletLogic();
    }

    /**
     * 用户钱包页面
     * User: hao  2020-8-27
     */
    public function wallet()
    {
        $data = $this->param;
        $rs = $this->walletLogic->wallet($this->api_user['id']);
        return $rs;
    }

    /**
     * 充值明细
     * User: hao  2020-8-28
     */
    public function recharge_detail()
    {
        $data = $this->param;
        $data['uid'] = $this->api_user['id'];

        $data['status'] = 1;
        $data['field'] = 'id,uid,order_sn,money,give_money,total_money,status,type,create_time,original_money,now_money';
        $list = (new UserRechargeLogModel())->getAllRecharge($data);
        $user_use_money = (new UserDetailsModel())->getValues(['uid'=>$data['uid']],'use_money');
        $data = array();
        $data['list'] = $list;
        $data['user_use_money'] = $user_use_money;
        return JsonUtils::successful('操作成功',$data);
    }


    /**
     * 积分明细
     * User: hao  2020-8-28
     */
    public function integral_detail(){
        $data = $this->param;
        //检验
        $validate = new UserApiValidate();
        $validate_resule = $validate->scene('integral_detail')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $data['uid'] = $this->api_user['id'];
        $list = (new UserIntegralLogModel())->getAllIntegral($data);
        $user_use_integral = (new UserDetailsModel())->getValues(['uid'=>$data['uid']],'use_integral');
        $data = array();
        $data['list'] = $list;
        $data['user_use_integral'] = $user_use_integral;
        return JsonUtils::successful('操作成功',$data);
    }

    /**
     * 提现明细
     * User: hao  2020-8-28
     */
    public function cash_detail(){
        $data = $this->param;
        $data['uid'] = $this->api_user['id'];
        $list = (new UserCashModel())->getAllCash($data);
        return JsonUtils::successful('操作成功',$list);
    }


    /**
     * 佣金明细
     * User: hao  2020-8-28
     */
    public function commission_detail(){
        $data = $this->param;
        //检验
        $validate = new UserApiValidate();
        $validate_resule = $validate->scene('commission_detail')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $data['uid'] = $this->api_user['id'];
        $list = (new UserCommissionLogModel())->getAllCommission($data);
        $user_commission = (new UserDetailsModel())->findInfo(['uid'=>$data['uid']],'commission_use_money,commission_frozen_money');
        $data = array();
        $data['list'] = $list;
        $data['commission_use_money'] = $user_commission['commission_use_money'];
        $data['commission_frozen_money'] = $user_commission['commission_frozen_money'];
        return JsonUtils::successful('操作成功',$data);
    }

    /**
     * 余额明细
     * User: hao  2020-8-28
     */
    public function balance_detail(){
        $data = $this->param;
        $data['uid'] = $this->api_user['id'];
        $list =  (new UserBalanceLogModel())->getAllBalance($data);

        $user_use_money = (new UserDetailsModel())->getValues(['uid'=>$data['uid']],'use_money');
        $cash_money =  (new UserRechargeCashModel())->findInfo(['uid'=>$data['uid']]);

        //可提现间隔时间 与手续费
        $website = new WebsiteConfigModel();
        $cash_time = $website->where('type', '=', 'cash_time')->value('val');
        $times = $cash_time * 24 * 60 * 60;
        $new_time = time();
        $money = 0;
        if (($new_time - $times) > $cash_money['time']) {
            $money = $cash_money['money'];
        }

        $data = array();
        $data['list'] = $list;
        $data['user_use_money'] = $user_use_money;
        $data['cash_money'] = $money;
        return JsonUtils::successful('操作成功',$data);
    }


    /**
     * 银行列表
     * User: hao  2020.09.08
     */
    public function get_bank(){
        return JsonUtils::successful('操作成功',['list'=>config('bank_code.lists')]);

    }


}