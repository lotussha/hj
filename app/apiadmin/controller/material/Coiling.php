<?php


namespace app\apiadmin\controller\material;


use app\apiadmin\controller\Base;
use app\apiadmin\model\AdminUsers;
use app\common\logic\material\CoilingLogic;
use app\common\model\material\CoilingModel;
use sakuno\utils\JsonUtils;
use app\common\validate\material\CoilingValidate;

//一键发圈

class Coiling extends Base
{
    /**
     * 一键发圈列表
     * User: hao
     * Date: 2020/8/8
     */
    public function index(CoilingModel $coilingModel){
        $data = $this->param;
        $data['identity_id'] = $this->admin_user['id'];
        $data['list_rows'] = $this->admin['list_rows'];
        $data['field'] = 'id,gid,img_url,video_url,title,copywriting,identity_id,sort,create_time';
        $list = $coilingModel->getAllCoiling($data);
        return JsonUtils::successful('获取成功',$list);
    }

    /**
     * 一键发圈详情
     * User: hao
     * Date: 2020/8/8
     */
    public function info(CoilingModel $coilingModel,CoilingValidate $validate){
        $validate_result = $validate->scene('info')->check($this->param);
        if (!$validate_result){
            return JsonUtils::fail($validate->getError(),00000);
        }
        $where = array();
        $where['id'] = $this->param['id'];
        $where['identity_id'] = $this->admin_user['id'];

        $field = 'id,gid,img_url,video_url,title,copywriting,identity_id,sort';
        //路由
        $lists = $coilingModel->getInfoCoiling($where,$field);
        return JsonUtils::successful('获取成功',$lists);
    }

    /**
     * 一键发圈添加
     * User: hao
     * Date: 2020/8/8
     */
    public function add(CoilingModel $coilingModel,CoilingValidate $validate){
        $data = $this->param;
        $validate_result = $validate->scene('add')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $aid = $this->admin_user['id'];
        //验证图片
        $Logic = new CoilingLogic();
        $data = $Logic->handle($data);
        if (isset($data['data_code']) && !$data['data_code']){
            return JsonUtils::fail($data['data_msg']);
        }
        $data['identity'] = (new AdminUsers())->where(['id'=>$aid])->value('identity');
        $data['identity_id'] = $aid;
        $arr = $coilingModel->addInfo($data);
        if ($arr){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败');
        }
    }

    /**
     * 一键发圈修改
     * User: hao
     * Date: 2020/8/8
     */
    public function edit(CoilingModel $coilingModel,CoilingValidate $validate){
        $data = $this->param;
        $validate_result = $validate->scene('edit')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),'00000');
        }

        //验证图片
        $Logic = new CoilingLogic();
        $data = $Logic->handle($data);
        if (isset($data['data_code']) && !$data['data_code']){
            return JsonUtils::fail($data['data_msg'],'00000');
        }
        $aid = $this->admin_user['id'];
        $arr = $coilingModel->updateInfo(['id'=>$data['id'],'identity_id'=>$aid],$data);

        if ($arr){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败','00000');
        }
    }

    /**
     * 一键发圈删除
     * User: hao
     * Date: 2020/8/8
     */
    public function del(CoilingModel $coilingModel,CoilingValidate $validate){
        $data = $this->param;
        $validate_result = $validate->scene('del')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),'00000');
        }

        $arr = $coilingModel->deleteInfo(['id'=>$data['id']]);

        if ($arr){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败','00000');
        }
    }


}