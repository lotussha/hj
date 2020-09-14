<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/1
 * Time: 10:58
 */

namespace app\apiadmin\validate;

use think\Validate;

class AdminUserValidate extends Validate
{
    protected $rule = [
        'id|id'      => 'require',
        'username|帐号'      => 'require|unique:admin_users',
        'role|角色' => 'require',
        'password|密码'  => 'require',
        'nickname|昵称' => 'require',
        'mobile|手机号'   => ['require', 'regex' => '/^1(3|4|5|7|8)[0-9]\d{8}$/'],
        'email|邮箱'     => 'email',
        'status|是否启用'  => 'require|in:1,2',
        'code|验证码'  => 'require',
    ];

    protected $message = [
        'email.email'  => '邮箱格式错误',
        'mobile.regex' => '手机格式错误',
    ];

    protected $scene = [
        'add'   => ['role', 'username', 'password', 'nickname'],
//        'edit'  => ['id','role', 'username', 'nickname'],
        'edit'  => ['id', 'username', 'nickname'],
        'login' => ['username'=>'require', 'password'],
        'del' => [ 'id'],
        'info'=>['id'],
        'status'=>['id','status'],
        'modify_password'=>['mobile','code','password'],
    ];

    public function sceneLogin()
    {
        $this->only(['username', 'password'])->remove('username', 'unique');
    }
}