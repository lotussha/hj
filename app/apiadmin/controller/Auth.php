<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/1
 * Time: 10:47
 */

namespace app\apiadmin\controller;

use app\apiadmin\model\AdminUsers;
use app\apiadmin\validate\AdminUserValidate;
use app\Request;
use Exception;
use app\apiadmin\logic\AdminAuthLogic;

class Auth extends Base
{
    protected $needAuth = false;

    /**
     * 登录
     * @param Request $request
     * @param AdminUsers $model
     * @param AdminUserValidate $validate
     * @return \think\response\Json
     * User: Jomlz
     * Date: 2020/8/1 11:49
     */
    public function login(Request $request,AdminUsers $model,AdminUserValidate $validate){
        $arr = array_return();
        $response = ['token' => ''];
        $param           = $request->param();
        $validate_result = $validate->scene('login')->check($param);
        if (!$validate_result) {
            $arr['status'] = '0';
            $arr['msg'] = $validate->getError();
            goto error;
        }
        try {
            $authLogic = new AdminAuthLogic();
            $res = $authLogic->authLogin($param);
        } catch (Exception $e) {
            $arr['status'] = '0';
            $arr['msg'] = $e->getMessage();
            goto error;
        }
        $res['role_type'] = '1';
        $token = signToken($res['id']);
        $response['token'] = $token;
        $response['user_info'] = $res;
        error:
        $arr['data'] = $response;
        apiLog(var_export($arr, true));
        return json($arr);
    }
}