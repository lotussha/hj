<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/1
 * Time: 16:14
 * 角色管理
 */

namespace app\apiadmin\controller;

use app\apiadmin\model\AdminMenuModel;
use app\apiadmin\model\AdminRoleModel;
use app\apiadmin\validate\AdminRoleValidate;
use app\common\logic\HandleLogic;

class AdminRole extends Base
{
    /**
     * 角色列表
     * User: Jomlz
     * Date: 2020/8/1 16:40
     */
    public function lists(AdminRoleModel $model)
    {
        $arr = array_return();
        $page = isset($this->param['page']) ? $this->param['page'] : 1;
        $lists = $model->field('id,name,description,status')->page($page,$this->admin['list_rows'])->select();

        $arr['data'] = arrString($lists);
        return return_json($arr);
    }

    /**
     * 角色信息
     * User: Jomlz
     * Date: 2020/8/1 16:45
     */
    public function info(AdminRoleModel $model,AdminRoleValidate $validate,AdminMenuModel $menuModel)
    {
        $arr = array_return();
        $validate_result = $validate->scene('info')->check($this->param);
        if (!$validate_result) {
            $arr['status'] = 0;
            $arr['msg'] = $validate->getError();
            goto error;
        }
//        $info = $model::find($this->param['id']);
//        if (!$info){
//            $arr['status'] = 0;
//            $arr['msg'] = '信息不存在';
//            goto error;
//        }
        $arr = $model->getRoleMenuList($this->param['id']);
        error:
        return return_json($arr);
    }

    public function add()
    {
        return $this->handle('add');
    }

    public function edit()
    {
       return $this->handle('edit');
    }
    public function del()
    {
        return $this->handle('del');
    }

    /**
     * 角色的增删改
     * @param string $act
     * @return \think\response\Json
     * User: Jomlz
     * Date: 2020/8/1 17:36
     */
    public function handle($act='')
    {
        $handleLogic = new HandleLogic;
        $validate = new AdminRoleValidate;
        $arr = array_return();
        $validate_result = $validate->scene($act)->check($this->param);
        if (!$validate_result) {
            $arr['status'] = 0;
            $arr['msg'] = $validate->getError();
            goto error;
        }
        $arr = $handleLogic->Handle($this->param,'AdminRoleModel',$act);
        error:
        return return_json($arr);
    }

}