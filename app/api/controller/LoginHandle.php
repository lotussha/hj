<?php


namespace app\api\controller;


use app\api\logic\LoginHandleLogic;
use app\common\model\user\UserModel;
use app\common\validate\user\UserApiValidate;
use app\common\validate\user\UserValidate;
use sakuno\utils\JsonUtils;
use Exception;
use think\facade\Db;

//用户登录
class LoginHandle extends Api
{
    protected $needAuth = false;

    /**
     * 用户授权登录
     * User: hao  2020-8-21
     */
    public function loginHome()
    {
        $data = $this->param;
        $validate = new UserApiValidate();
        $validate_result = $validate->scene('login')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(), 10001);
        }
        try {
            $logic = new LoginHandleLogic();
            $rs = $logic->Login($data);
            return $rs;
        } catch (Exception $e) {
//            return JsonUtils::fail($e->getMessage(), 10001);
            return JsonUtils::fail('服务器繁忙');
        }

    }


    /**
     * 用户授权登录+手机号
     * User: hao  2020-9-4
     */
    public function loginPhone()
    {

        $data = $this->param;

        $validate = new UserApiValidate();
        $validate_result = $validate->scene('loginphone')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(), 10001);
        }
        Db::startTrans();
        try {
        $logic = new LoginHandleLogic();

        $rs = $logic->login_phone($data);
        Db::commit();
        return $rs;
        } catch (Exception $e) {
//            return JsonUtils::fail($e->getMessage(), 10001);
            return JsonUtils::fail('服务器繁忙');
        }
    }


    /**
     * 用户绑定
     * User: hao  2020-8-22
     */
    public function bind_phone()
    {
        $data = $this->param;
        $validate = new UserApiValidate();
        $validate_result = $validate->scene('bind')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(), 10001);
        }
        $User = new UserModel();
        try {
            $logic = new LoginHandleLogic();
            $rs = $logic->bind($data);
            return $rs;
        } catch (Exception $e) {
//            return JsonUtils::fail($e->getMessage(), 10001);
            $User->rollbackTrans(); //提交事务
            return JsonUtils::fail('服务器繁忙');
        }
    }


    /**
     * 获取验证码
     * User: hao  2020-8-25
     */
//    public function getPhoneSmsCode()
//    {
//        $data = $this->param;
//        $validate = new UserApiValidate();
//        $validate_result = $validate->scene('code')->check($data);
//        if (!$validate_result) {
//            return JsonUtils::fail($validate->getError(), 10001);
//        }
//        $logic = new LoginHandleLogic();
//        $rs = $logic->code($data);
//        return $rs;
//
//    }


    /**
     * 虚拟登录
     * User: hao  2020-8-22
     */
    public function fictitiousLogin()
    {
        $User = new UserModel();
//        $userInfo = $User->findInfo(['id' => 38], 'id,share_id,username,grade_id,nick_name,phone,avatar_url,status,openid');
        $uid = '1';
        $logic = new LoginHandleLogic();
        //返回数据
        return $logic->handleUserLoginCache($uid);
    }


}