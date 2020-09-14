<?php


namespace app\apiadmin\logic\admin;


use app\apiadmin\model\AdminUsers;
use app\common\model\settlement\SettlementModel;
use sakuno\utils\JsonUtils;
use think\facade\Db;

class AdminUserLogic
{

    /**
     * 修改管理员信息
     * User: hao修改
     * Date: 2020.09.07
     */
    public function edit($receive)
    {
        try {
            Db::startTrans();
            $AdminUsers = (new AdminUsers())::find($receive['id']);
            $res = $AdminUsers->save($receive);
            if ($res === false) {
                Db::rollback();
                return JsonUtils::fail('修改失败');
            }
            $settlement = (new SettlementModel())->findInfo(['admin_id'=>$receive['id']]);
            if ($settlement){
                $settlement_data = array();
                $settlement_data['username'] = $receive['username'];
                $settlement_data['nickname'] = $receive['nickname'];
                $settlement_data['logo_img'] = $receive['avatar'];
                $res = $settlement->save($settlement_data);
                if ($res === false) {
                    Db::rollback();
                    return JsonUtils::fail('修改失败');
                }
            }
            Db::commit();
            return JsonUtils::successful('操作成功');
        } catch (\Exception $e) {
            return JsonUtils::fail('服务器异常');
        }
    }
}