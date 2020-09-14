<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/7/31
 * Time: 16:17
 * 菜单管理
 */

namespace app\apiadmin\controller;

use app\apiadmin\model\AdminMenuModel;
use app\apiadmin\validate\AdminMenuValidate;
use app\common\logic\HandleLogic;

class AdminMenu extends Base
{
    /**
     * 菜单列表
     * User: Jomlz
     * Date: 2020/7/31 16:58
     */
    public function lists(AdminMenuModel $model)
    {
        $arr = array_return();
        $response = [
            'lists' => array()
        ];
        $data = $model->order('sort_id asc,id asc')->column('id,parent_id,name,url,sort_id,is_show,icon','id');
        $lists = getTree($data);
        $response['lists'] = $lists;
        $arr['data'] = $response;
        return return_json($arr);
    }

    /**
     * 菜单信息
     * User: Jomlz
     * Date: 2020/8/1 14:39
     */
    public function info(AdminMenuModel $model,AdminMenuValidate $validate)
    {
        $arr = array_return();
        $response = [
            'info' => (object)array(),
        ];
        $validate_result = $validate->scene('info')->check($this->param);
        if (!$validate_result) {
            $arr['status'] = 0;
            $arr['msg'] = $validate->getError();
            goto error;
        }
        $info = $model->find($this->param['id']);
        if (!$info){
            $arr['status'] = 0;
            $arr['msg'] = '信息不存在';
            goto error;
        }
        $response['info'] = turnString($info->toArray());
        error:
        $arr['data'] = $response;
        return return_json($arr);

    }

    /**
     * 添加菜单
     * User: Jomlz
     * Date: 2020/7/31 18:23
     */
    public function add(HandleLogic $handleLogic,AdminMenuValidate $validate)
    {
        return $this->handle('add');
    }

    /**
     * 编辑菜单
     * User: Jomlz
     * Date: 2020/8/1 15:29
     */
    public function edit(HandleLogic $handleLogic,AdminMenuValidate $validate)
    {
        return $this->handle('edit');
    }

    /**
     * 删除菜单
     * User: Jomlz
     * Date: 2020/8/1 15:58
     */
    public function del(HandleLogic $handleLogic,AdminMenuValidate $validate)
    {
        return $this->handle('del');
    }

    public function handle($act='')
    {
        $handleLogic = new HandleLogic;
        $validate = new AdminMenuValidate;

        $arr = array_return();
        $validate_result = $validate->scene($act)->check($this->param);
        if (!$validate_result) {
            $arr['status'] = 0;
            $arr['msg'] = $validate->getError();
            goto error;
        }
        $arr = $handleLogic->Handle($this->param,'AdminMenuModel',$act);
        error:
        return return_json($arr);
    }
}