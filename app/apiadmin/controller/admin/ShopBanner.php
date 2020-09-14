<?php


namespace app\apiadmin\controller\admin;


use app\apiadmin\controller\Base;
use app\apiadmin\model\AdminUsers;
use app\common\logic\advertisement\BannerLogic;
use app\common\model\advertisement\BannerModel;
use app\common\validate\advertisement\BannerValidate;
use sakuno\utils\JsonUtils;

//店铺的轮播图
class ShopBanner extends Base
{
    /**
     * 轮播图列表
     * User: hao
     * Date: 2020.09.07
     */
    public function lists(){
        $data = $this->param;
        $data['identity_id'] = $this->admin_user['identity_id'];
        $data['field'] ='id,link_id,img_url,background,sort,status,skip_type';
        $list = (new BannerModel())->getAllBanner($data);
        return JsonUtils::successful('操作成功',$list);
    }

    /**
     * 轮播图详情
     * User: hao
     * Date: 2020.09.07
     */
    public function info(){
        $data = $this->param;
        $data['identity_id'] = $this->admin_user['identity_id'];

        $validate = new BannerValidate();
        $validate_result = $validate->scene('info')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $field ='id,link_id,img_url,background,sort,status,skip_type';

        $list = (new BannerModel())->getInfoBanner($data,$field);
        return JsonUtils::successful('操作成功',$list);
    }



    /**
     * 轮播图添加
     * User: hao
     * Date: 2020.09.07
     */
    public function add(){
        $data = $this->param;
        $data['identity_id'] = $this->admin_user['identity_id'];
        $data['identity'] = $this->admin_user['identity'];
        $validate = new BannerValidate();
        $validate_result = $validate->scene('add')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $logic = new BannerLogic();
        $data = $logic->Handle($data);

        $res = (new BannerModel())->addInfo($data);
        if (!$res){
            return JsonUtils::fail('操作失败');
        }
        return JsonUtils::successful('操作成功');
    }


    /**
     * 轮播图修改
     * User: hao
     * Date: 2020.09.07
     */
    public function edit(){
        $data = $this->param;
        $data['identity_id'] = $this->admin_user['identity_id'];
        $validate = new BannerValidate();
        $validate_result = $validate->scene('edit')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $logic = new BannerLogic();
        $data = $logic->Handle($data);

        $res = (new BannerModel())->updateInfo(['id'=>$data['id'],'identity_id'=>$data['identity_id']],$data);
        if ($res===false){
            return JsonUtils::fail('操作失败');
        }
        return JsonUtils::successful('操作成功');
    }

    /**
     * 轮播图禁用/启用
     * User: hao
     * Date: 2020.09.07
     */
    public function status(){
        $data = $this->param;
        $data['identity_id'] = $this->admin_user['identity_id'];
        $validate = new BannerValidate();
        $validate_result = $validate->scene('status')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $res = (new BannerModel())->updateInfo(['id'=>$data['id'],'identity_id'=>$data['identity_id']],['status'=>$data['status']]);
        if ($res===false){
            return JsonUtils::fail('操作失败');
        }
        return JsonUtils::successful('操作成功');
    }

    /**
     * 轮播图删除
     * User: hao
     * Date: 2020.09.07
     */
    public function del(BannerModel $bannerModel){
        $identity_id =  $this->admin_user['identity_id'];
        //检验
        $validate = new BannerValidate();
        $validate_result = $validate->scene('del')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }

        //路由
        $lists = $bannerModel->deleteInfo(['id'=>$this->param['id'],'identity_id'=>$identity_id]);
        if ($lists===false){
            return JsonUtils::fail('操作失败');
        }

        return JsonUtils::successful('操作成功');
    }
}