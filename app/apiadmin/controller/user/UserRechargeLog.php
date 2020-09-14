<?php


namespace app\apiadmin\controller\user;


use app\apiadmin\controller\Base;
use app\common\model\user\UserRechargeLogModel;
use sakuno\utils\JsonUtils;

//用户充值记录
class UserRechargeLog extends Base
{
    /**
     * 用户充值记录
     * @return array
     * @author hao    2020.08.19
     * */
    public function index(){
        $data =$this->param;
        $data['list_rows'] = $this->admin['list_rows'];
        $model = new UserRechargeLogModel();
        $data['field'] ='id,uid,order_sn,money,give_money,total_money,status,type,total_money,create_time,remark,original_money,now_money';
        $res = $model->getAllRecharge($data);
        return JsonUtils::successful('操作成功',$res);
    }
}