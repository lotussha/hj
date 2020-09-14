<?php


namespace app\common\logic\settlement;

use app\common\model\settlement\SettlementModel;
use app\apiadmin\model\AdminUsers;
use sakuno\utils\JsonUtils;

//入驻
class SettlementLogic
{
    //入驻添加处理
    public function Handle($data, $act = 'add')
    {
        $AdminUsers = new AdminUsers();

        $where = array();
        $where[] = ['username', '=', $data['username']];

        $rs = $AdminUsers->where($where)->value('id');
        if ($rs) {
            return false;
        }
        $wheres = array();
        $wheres[] = ['username', '=', $data['username']];
        if ($act == 'edit') {
            $wheres[] = ['id', '<>', $data['id']];
        }

        $res = (new SettlementModel())->getValues($wheres, 'id');

        if ($res) {
            return false;
        }


        //密码加密
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], 1);
        }
        return $data;
    }


    //后台添加入驻
    public function AddHandle($receive)
    {
        $SettlementModel = new SettlementModel();
        $AdminUsers = new AdminUsers();

        $receive['examine_is'] = 1;
        $receive['examine_time'] = time();

        if (($receive['identity'] == 2 || $receive['identity'] == 3) && !isset($receive['business_license'])) {
            return JsonUtils::fail('营业执照不能为空');

        }

        //判断相同的账号
        $receive = $this->Handle($receive);
        if (!$receive) {
            return JsonUtils::fail('已有相同的账号');
        }

        try {
            $SettlementModel->beginTrans();
            $id = $SettlementModel->addInfoId($receive);
            if (!$id) {
                $SettlementModel->rollbackTrans();
                return JsonUtils::fail('操作失败');
            }

            $admin_data = array();
            $admin_data['username'] = $receive['username'];
            $admin_data['password'] = $receive['password'];
            $admin_data['nickname'] = $receive['nickname'];
            $admin_data['identity'] = $receive['identity'];
            $admin_data['avatar'] = $receive['logo_img'];
            $admin_data['s_id'] = $id;
            $aid = $AdminUsers->insertGetId($admin_data);
            if (!$aid) {
                $SettlementModel->rollbackTrans();
                return JsonUtils::fail('操作失败1');
            }
            $res = $SettlementModel->where(['id' => $id])->update(['admin_id' => $aid]);
            if ($res === false) {
                $SettlementModel->rollbackTrans();
                return JsonUtils::fail('操作失败3');
            }
            $SettlementModel->commitTrans();
            return JsonUtils::successful('操作成功');
        } catch (\Exception $e) {
            $SettlementModel->rollbackTrans();
            return JsonUtils::fail('操作失败2');

        }
    }

    //后台修改
    public function EditHandle($receive)
    {
        $settlementModel = new SettlementModel();

        $list = $settlementModel->findInfo(['id' => $receive['id']], 'examine_is');

        if (!$list) {
            return JsonUtils::fail('暂无入驻信息');
        }
        if (($receive['identity'] == 2 || $receive['identity'] == 3) && !isset($receive['business_license'])) {
            return JsonUtils::fail('营业执照不能为空');

        }

        //不成功才能修改的，成功不能修改
        if ($list['examine_is'] == 1) {
            unset($receive['username']);
            unset($receive['password']);
            unset($receive['nickname']);
            unset($receive['examine_is']);
        }

        $arr = $settlementModel->updateInfo(['id' => $receive['id']], $receive);

        if ($arr) {
            return JsonUtils::successful('操作成功');
        } else {
            return JsonUtils::fail('操作失败', '00000');
        }

    }


    //审核
    public function ExamineHandle($receive)
    {
        $settlementModel = new SettlementModel();
        $AdminUsers = new AdminUsers();

        $arr_data = array();
        $arr_data['examine_is'] = $receive['examine_is'];
        $arr_data['examine_remarks'] = isset($receive['examine_remarks']) ? $receive['examine_remarks'] : '';
        $arr_data['examine_time'] = time();

        $settlementList = $settlementModel->findInfo(['id' => $receive['id']], 'username,password,nickname,identity,examine_is');

        if ($settlementList['examine_is'] == 1) {
            return JsonUtils::fail('已审核通过。请勿操作');
        }
        try {
            $settlementModel->beginTrans();
            $res = $settlementModel->updateInfo(['id' => $receive['id']], $arr_data);
            if (!$res) {
                $settlementModel->rollbackTrans();
                return JsonUtils::fail('操作失败');
            }

            //审核成功
            if ($receive['examine_is'] == 1) {
                $admin_data = array();
                $admin_data['username'] = $settlementList['username'];
                $admin_data['password'] = $settlementList['password'];
                $admin_data['nickname'] = $settlementList['nickname'];
                $admin_data['identity'] = $settlementList['identity'];
                $admin_data['avatar'] = $receive['logo_img'];
                $admin_data['s_id'] = $receive['id'];
                $aid = $AdminUsers->insertGetId($admin_data);
                if (!$aid) {
                    $settlementModel->rollbackTrans();
                    return JsonUtils::fail('操作失败');
                }
                $res = $settlementModel->where(['id' => $receive['id']])->update(['admin_id' => $aid]);
                if ($res === false) {
                    $settlementModel->rollbackTrans();
                    return JsonUtils::fail('操作失败');
                }
            }
            $settlementModel->commitTrans();
            return JsonUtils::successful('操作成功');
        } catch (\Exception $e) {
            $settlementModel->rollbackTrans();
            return JsonUtils::fail('操作失败1');
        }
    }


    //前端添加
    public function userAddHandle($receive)
    {
        $id = (new SettlementModel())->getValues(['uid' => $receive['uid']], 'id');
        if ($id) {
            return JsonUtils::fail('您已申请入驻，请勿重复申请');
        }

        //        $OperateRedis = new OperateRedis(); //封装的redis模型
//        $cache_app_code = $OperateRedis->get('cache_app_code:' . $receive['username']); //获取手机验证码

        if (!isset($receive['code'])) {
            return JsonUtils::fail('请输入验证码');

        }
        //验证码
//        if ($receive['code'] != $cache_app_code) {
//            return JsonUtils::fail('验证码已失效');
//        }

        $receive = $this->Handle($receive);
        if (!$receive) {
            return JsonUtils::fail('账号已存在');
        }
        if ($receive['identity'] == 3 && !isset($receive['business_license'])) {
            return JsonUtils::fail('营业执照不能为空');
        }

        unset($receive['code']);
        $res = (new SettlementModel())->addInfo($receive);
        if (!$res) {
            return JsonUtils::successful('入驻失败');
        }
        return JsonUtils::successful('入驻申请成功');
    }

    //前端修改
    public function userEditHandle($receive)
    {
        $list = (new SettlementModel())->findInfo(['uid' => $receive['uid'], 'id' => $receive['id']], 'examine_is');
        if (!$list) {
            return JsonUtils::fail('用户未申请入驻，已申请入驻');
        }
        if ($list['examine_is'] != 2) {
            return JsonUtils::fail('用户审核中，或者已入驻，请勿修改');
        }


        //        $OperateRedis = new OperateRedis(); //封装的redis模型
        //        $cache_app_code = $OperateRedis->get('cache_app_code:' . $receive['username']); //获取手机验证码

        if (!isset($receive['code'])) {
            return JsonUtils::fail('请输入验证码');

        }
        //验证码
//        if ($receive['code'] != $cache_app_code) {
//            return JsonUtils::fail('验证码已失效');
//        }

        $receive = $this->Handle($receive, 'edit');

        if (!$receive) {
            return JsonUtils::fail('账号已存在');
        }

        if ($receive['identity'] == 3 && !isset($receive['business_license'])) {
            return JsonUtils::fail('营业执照不能为空');
        }

        unset($receive['code']);
        $res = (new SettlementModel())->updateInfo(['id' => $receive['id']], $receive);
        if (!$res) {
            return JsonUtils::successful('修改入驻失败');
        }
        return JsonUtils::successful('修改入驻申请成功');

    }


}