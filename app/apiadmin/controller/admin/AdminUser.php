<?php


namespace app\apiadmin\controller\admin;


use app\apiadmin\controller\Base;
use app\apiadmin\logic\admin\AdminUserLogic;
use app\apiadmin\model\AdminUsers;
use app\apiadmin\validate\AdminUserValidate;
use sakuno\utils\JsonUtils;

//管理员
class AdminUser extends Base
{
    /**
     * 管理员列表
     * User: hao
     * Date: 2020.09.05
     */
    public function lists(){
        $data = $this->param;

        $data['field'] = 'id,username,nickname,avatar,status,identity';
        $where = array();
        $is_shop = $data['is_shop']??'0';

        if ($is_shop){
            $where[] = ['identity','in','2,3,4'];
        }
        $data['where'] = $where;
        $list = (new AdminUsers())->getAllAdmin($data);
        return JsonUtils::successful('操作成功',$list);
    }

    /**
     * 管理员增加
     * User: hao
     * Date: 2020.09.07
     */
    public function add(){
        $data=$this->param;
        $validate = new AdminUserValidate();
        $validate_result = $validate->scene('add')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $data['create_time'] = time();
        $res = (new AdminUsers())->create($data);
        if (!$res){
            return JsonUtils::fail('添加失败');
        }
        return JsonUtils::successful('添加成功');
    }

    /**
     * 管理员修改
     * User: hao
     * Date: 2020.09.07
     */
    public function edit(){
        $data=$this->param;
        $validate = new AdminUserValidate();
        $validate_result = $validate->scene('edit')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $logic = new AdminUserLogic();
        $res = $logic->edit($data);
        return $res;
    }

    /**
     * 管理员详情
     * User: hao
     * Date: 2020.09.07
     */
    public function info(){
        $data=$this->param;
        $validate = new AdminUserValidate();
        $validate_result = $validate->scene('info')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $info = (new AdminUsers())->field('id,username,nickname,avatar,role')->find($data['id']);
        return JsonUtils::successful('操作成功',['list'=>$info]);
    }

    /**
     * 管理员禁用
     * User: hao
     * Date: 2020.09.07
     */
    public function status(){
        $data=$this->param;
        $validate = new AdminUserValidate();
        $validate_result = $validate->scene('status')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $res = (new AdminUsers())->where(['id'=>$data['id']])->update(['status'=>$data['status']]);
        if ($res===false){
            return JsonUtils::fail('操作失败');
        }
        return JsonUtils::successful('操作成功');
    }

}