<?php


namespace app\common\logic\apiadmin;


use app\apiadmin\model\AdminUsers;
use sakuno\utils\JsonUtils;

//店铺登录
class LoginAdminLogic
{
    /**
     * 小程序门店登录
     * @param array $data
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * User: hao
     * Date: 2020.09.05
     */
    public function wxLogin($receive = array())
    {

        $model = new AdminUsers;
        $adminUser = $model->where('username', $receive['username'])->field('id,username,nickname,avatar,identity,password,status,identity')->find();
        if (!$adminUser) {
            return JsonUtils::fail('用户不存在');
        }

        if (!password_verify($receive['password'], $adminUser->password)) {
            return JsonUtils::fail('密码错误');
        }

        if ((int)$adminUser->status !== 1) {
            return JsonUtils::fail('用户被冻结');
        }


        //只有供应商、门店、团长 能登录
        if (!in_array($adminUser['identity'], ['2', '3', '4'])) {
            return JsonUtils::fail('无权限登录');
        }
        $adminUserInfo = [
            'id' => $adminUser->id,
            'username' => $adminUser->username,
            'nickname' => $adminUser->nickname,
            'avatar' => $adminUser->avatar,
            'identity' => $adminUser->identity,
        ];

        //token
        $token = signToken($adminUser['id']);
        $data = array();
        $data['token'] = $token;
        $data['admin_info'] = $adminUserInfo;
        return JsonUtils::successful('操作成功', $data);
    }

    /**
     * 门店修改密码
     * @param array $data
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * User: hao
     * Date: 2020.09.08
     */
    public function modify_password($receive = array())
    {
        //        $OperateRedis = new OperateRedis(); //封装的redis模型
//        $cache_app_code = $OperateRedis->get('cache_app_code:' . $receive['mobile']); //获取手机验证码

        //验证码
//        if ($receive['code'] != $cache_app_code) {
//            return JsonUtils::fail('验证码已失效');
//        }

        $admin = (new AdminUsers())->where(['username'=>$receive['mobile']])->find();
        if ($admin['status']==2){
            return JsonUtils::fail('账号已禁用不能修改');
        }

        if (!in_array($admin['identity'],['2','3','4'])){
            return JsonUtils::fail('该账号不是店铺不能修改');
        }

        $password = password_hash($receive['password'], 1);

        $res = (new AdminUsers())->where(['username'=>$receive['mobile']])->update(['password'=>$password]);
        if ($res===false){
            return JsonUtils::fail('操作失败');
        }
        return JsonUtils::successful('操作成功');

    }
}