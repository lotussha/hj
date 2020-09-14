<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/1
 * Time: 9:19
 * 权限认证类
 */

namespace tools;

use app\apiadmin\model\AdminUsers;

trait AdminAuth
{
    /**
     * 是否登录
     * User: Jomlz
     * Date: 2020/8/1 9:44
     */
    public function isLogin()
    {
        return false;
    }


    /**
     * 权限检查
     * @param $user AdminUsers
     * @return bool
     * User: Jomlz
     * Date: 2020/8/1 14:10
     */
    public function authCheck($user)
    {
        return in_array($this->url, $this->authExcept, true) || in_array($this->url, $user->auth_url, true);
    }
}