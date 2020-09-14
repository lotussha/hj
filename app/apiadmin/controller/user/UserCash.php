<?php


namespace app\apiadmin\controller\user;


use app\apiadmin\controller\Base;
use app\common\logic\user\UserLogic;
use app\common\model\user\UserCashModel;
use app\common\validate\user\UserCashValidate;
use app\common\validate\user\UserValidate;
use app\Request;
use sakuno\services\UtilService;
use sakuno\utils\JsonUtils;
use think\Route;
//用户提现
class UserCash extends Base
{
    /**
     * 用户提现
     * @return array
     * @author hao    2020.08.19
     * */
    public function index(){
        $data =$this->param;
        $data['list_rows'] = $this->admin['list_rows'];
        $model = new UserCashModel();
        $data['field'] ='id,uid,order_sn,money,charge,account_money,type,create_time,examine_is,examine_remarks,alipay_username,idcard_user,card';
        $res = $model->getAllCash($data);
        return JsonUtils::successful('操作成功',$res);
    }


    /**
     * 审核
     * @return array
     * @author hao    2020.08.19
     * */
    public function examine(Request $request){
        list($id,$examine_is,$examine_remarks) = UtilService::postMore([
            ['id', ''],
            ['examine_is', ''],
            ['examine_remarks', ''],
        ], $request, true);

        $data = array();
        $data['id'] = $id;
        $data['examine_is'] = $examine_is;
        $data['examine_remarks'] = $examine_remarks;
        $data['examine_time'] = time();
        $validate = new UserCashValidate();
        //检验
        $validate_resule = $validate->scene('examine')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $logic = new UserLogic();
        $res = $logic->cash_examine($data);
        return $res;

    }
}