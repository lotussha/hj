<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/1
 * Time: 10:51
 */

namespace app\apiadmin\logic;

use app\apiadmin\model\AdminUsers;

class AdminAuthLogic
{
    /**
     * 用户登录
     * @param array $data
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * User: Jomlz
     * Date: 2020/8/1 11:43
     */
    public function authLogin($data=array())
    {
        $model = new AdminUsers;
        $adminUser = $model->where('username', $data['username'])->find();
        if (!$adminUser){
            exception('用户不存在');
        }
        if (!password_verify($data['password'], $adminUser->password)) {
            exception('密码错误');
        }
        if ((int)$adminUser->status !== 1) {
            exception('用户被冻结');
        }
        $adminUserInfo = [
            'id' => $adminUser->id,
            'username' => $adminUser->username,
            'nickname' => $adminUser->nickname,
            'avatar' => $adminUser->avatar,
            'identity'=>$adminUser->identity,
        ];
        return turnString($adminUserInfo);
    }




}