<?php


namespace app\api\controller;

//店铺登录
use app\apiadmin\logic\AdminAuthLogic;
use app\apiadmin\validate\AdminUserValidate;
use app\common\logic\apiadmin\LoginAdminLogic;
use sakuno\utils\JsonUtils;

class LoginAdmin  extends ApiAdmin
{
    protected $needAuth = false;

    /**
     * 店铺登录
     * User: hao  2020-09-05
     */
    public function login(){
        $data = $this->param;
        $validate = new AdminUserValidate();
        //验证器
        $validate_result = $validate->scene('login')->check($data);

        if (!$validate_result){
            return JsonUtils::fail($validate->getError());
        }

        $authLogic = new LoginAdminLogic();
//        $admin = $authLogic->authLogin($data);
        $res = $authLogic->wxLogin($data);
        return $res;
    }


    /**
     * 店铺修改密码
     * User: hao  2020-09-09
     */
    public function modify_password(){
        $data = $this->param;
        $validate = new AdminUserValidate();
        //验证器
        $validate_result = $validate->scene('modify_password')->check($data);

        if (!$validate_result){
            return JsonUtils::fail($validate->getError());
        }
        $logic = new LoginAdminLogic();
        $res = $logic->modify_password($data);
        return $res;
    }

}