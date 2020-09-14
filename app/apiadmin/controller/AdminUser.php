<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/1
 * Time: 18:11
 * 用户管理
 */

namespace app\apiadmin\controller;

use app\apiadmin\model\AdminUsers;
use app\apiadmin\validate\AdminUserValidate;
use app\common\logic\HandleLogic;
use think\facade\Db;

class AdminUser extends Base
{
    /**
     * 用户列表
     * User: Jomlz
     * Date: 2020/8/1 18:49
     */
    public function lists(AdminUsers $model)
    {
        $arr = array_return();
        $page = isset($this->param['page']) ? $this->param['page'] : 1;
        $lists = $model->field('id,username,nickname,avatar,role,status')->page($page,$this->admin['list_rows'])->select();
        foreach ($lists as $k=>$v){
            $role_list = array();
            foreach ($v->role_text as $kk=>$vv){
                array_push($role_list,$vv);
            };
            $lists[$k]['role_list'] = $role_list;
            unset($lists[$k]['role']);
        }
        $arr['data'] = arrString($lists);
        apiLog(var_export($arr, true));
        return json($arr);
    }

    public function info(AdminUsers $model,AdminUserValidate $validate)
    {
        $arr = array_return();
        $role = Db::name('admin_role')->column('id,name','id');
        $info['role'] = [];
        if (isset($this->param['id']) && !empty($this->param['id'])){
            $info = $model->field('id,username,nickname,avatar,role')->find($this->param['id']);
            if (!$info){
                $arr['status'] = 0;
                $arr['msg'] = '信息不存在';
                goto error;
            }
        }
        foreach ($role as $k=>$v){
            if (in_array($v['id'],$info['role'])){
                $role[$k]['is_checked'] = 1;
            }else{
                $role[$k]['is_checked'] = 0;
            }
        }
        $arr['data'] = ['info'=>turnString($info),'role_list'=>arrString(array_values($role))];
        error:
        apiLog(var_export($arr, true));
        return json($arr);
    }

    public function add()
    {
        return $this->handle('add');
    }

    public function edit()
    {
        if (isset($this->param['password']) && empty($this->param['password'])){
            unset($this->param['password']);
        }
        return $this->handle('edit');
    }
    public function del()
    {
        return $this->handle('del');
    }

    public function handle($act='')
    {
        $handleLogic = new HandleLogic();
        $validate = new AdminUserValidate();
        $arr = array_return();
        $validate_result = $validate->scene($act)->check($this->param);
        if (!$validate_result) {
            $arr['status'] = 0;
            $arr['msg'] = $validate->getError();
            goto error;
        }
        $arr = $handleLogic->Handle($this->param,'AdminUsers',$act);
        error:
        apiLog(var_export($arr, true));
        return json($arr);
    }
}