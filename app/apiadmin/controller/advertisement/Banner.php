<?php


namespace app\apiadmin\controller\advertisement;

use app\apiadmin\controller\Base;
use app\common\model\advertisement\BannerPositionModel;
use app\common\validate\advertisement\BannerValidate;
use app\common\validate\advertisement\BannerPositionValidate;
use sakuno\utils\JsonUtils;
use think\Collection;
use app\common\model\advertisement\BannerModel;
use app\common\logic\advertisement\BannerLogic;
use app\common\logic\advertisement\BannerPositionLogic;

//轮播图
class Banner extends Base
{
    /**
     * 轮播图列表
     * User: hao
     * Date: 2020/8/8
     */
    public function banner_lists(BannerModel $bannerModel){
        $data =$this->param;
        $data['list_rows'] = $this->admin['list_rows'];
        $data['identity_id'] = 0;
        $data['field'] = 'id,name,img_url,type,status,sort,link_id,img_url,position_id,start_time,end_time,background,skip_type';
        $list = $bannerModel->getAllBanner($data);
        return JsonUtils::successful('获取成功',$list);
    }

    /**
     * 轮播图详情
     * User: hao
     * Date: 2020/8/8
     */
    public function banner_info(BannerModel $bannerModel){
        //检验
        $validate = new BannerValidate;
        $validate_result = $validate->scene('info')->check($this->param);
        if (!$validate_result){
            return JsonUtils::fail($validate->getError(),'00000');
        }

        //路由
        $lists = $bannerModel->getInfoBanner(['id'=>$this->param['id']]);
        return JsonUtils::successful('获取成功',$lists);

    }

    /**
     * 轮播图添加
     * User: hao
     * Date: 2020/8/8
     */
    public function banner_add(BannerModel $bannerModel){
        $data = $this->param;
        //检验
        $validate = new BannerValidate;
        $validate_result = $validate->scene('add')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),'00000');
        }

        //把时间格式转换
        $BannerLogic = new BannerLogic;
        $data = $BannerLogic->Handle($data);

        //模型
        $arr =$bannerModel->addInfo($data);

        if ($arr){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败','00000');
        }

    }

    /**
     * 轮播图修改
     * User: hao
     * Date: 2020/8/8
     */
    public function banner_edit(BannerModel $bannerModel){

        $data = $this->param;
        //检验
        $validate = new BannerValidate;
        $validate_result = $validate->scene('edit')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),'00000');
        }

        //把时间格式转换
        $BannerLogic = new BannerLogic;
        $data = $BannerLogic->Handle($data);

        //模型
        $arr =$bannerModel->updateInfo(['id'=>$this->param['id']],$data);

        if ($arr){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败','00000');
        }
    }


    /**
     * 轮播图禁用/启用
     * User: hao
     * Date: 2020.09.07
     */
    public function banner_status(){
        $data = $this->param;
        $validate = new BannerValidate();
        $validate_result = $validate->scene('status')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $res = (new BannerModel())->updateInfo(['id'=>$data['id']],['status'=>$data['status']]);
        if ($res===false){
            return JsonUtils::fail('操作失败');
        }
        return JsonUtils::successful('操作成功');
    }


    /**
     * 轮播图删除
     * User: hao
     * Date: 2020/8/8
     */
    public function banner_del(BannerModel $bannerModel){
        //检验
        $validate = new BannerValidate();
        $validate_result = $validate->scene('del')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),'00000');
        }

        //路由
        $lists = $bannerModel->deleteInfo(['id'=>$this->param['id']]);
        if (!$lists){
            return JsonUtils::fail('操作失败','00000');
        }

        return JsonUtils::successful('操作成功');
    }

    /**
     * 轮播图位置列表
     * User: hao
     * Date: 2020/8/8
     */
    public function position_lists(BannerPositionModel $bannerPositionModel){

        $data =$this->param;
        $data['list_rows'] = $this->admin['list_rows'];
        $data['field'] = 'id,name,width,height,describe,status,sort';
        $res = $bannerPositionModel->getPositionList($data);
        return JsonUtils::successful('操作成功',$res);

    }

    /**
     * 轮播图位置详情
     * User: hao
     * Date: 2020/8/8
     */
    public function position_info(BannerPositionModel $bannerPositionModel){

        //检验
        $validate = new BannerPositionValidate;
        $validate_result = $validate->scene('info')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),'00000');
        }

        //路由
        $field = 'id,name,width,height,describe,status,sort';
        $lists = $bannerPositionModel->getPositionInfo(['id'=>$this->param['id']],$field);
        return JsonUtils::successful('获取成功',$lists);
    }

    /**
     * 轮播图位置添加
     * User: hao
     * Date: 2020/8/8
     */
    public function position_add(BannerPositionModel $bannerPositionModel){

        $data = $this->param;
        //检验
        $validate = new BannerPositionValidate();
        $validate_result = $validate->scene('add')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),'00000');
        }
        //过滤相同名称
        $logic = new BannerPositionLogic();
        $data = $logic->Handle($data);
        if (!$data){
            return JsonUtils::fail('已有相同的名称','00000');
        }
        //模型
        $arr =$bannerPositionModel->addInfo($data);

        if ($arr){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败','00000');
        }
    }

    /**
     * 轮播图位置修改
     * User: hao
     * Date: 2020/8/8
     */
    public function position_edit(BannerPositionModel $bannerPositionModel){
        $data = $this->param;
        //检验
        $validate = new BannerPositionValidate();
        $validate_result = $validate->scene('edit')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),'00000');
        }
        //过滤相同名称
        $logic = new BannerPositionLogic();
        $data = $logic->Handle($data,'edit');
        if (!$data){
            return JsonUtils::fail('已有相同的名称','00000');
        }
        //模型
        $arr =$bannerPositionModel->updateInfo(['id'=>$data['id']],$data);

        if ($arr){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败','00000');
        }
    }

    /**
     * 轮播图位置删除
     * User: hao
     * Date: 2020/8/8
     */
    public function position_del( BannerPositionModel $bannerPositionModel){
        //检验
        $validate = new BannerPositionValidate;
        $validate_result = $validate->scene('del')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),'00000');
        }
        //路由
        $lists = $bannerPositionModel->deleteInfo(['id'=>$this->param['id']]);
        if (!$lists){
            return JsonUtils::fail('操作失败','00000');
        }
        return JsonUtils::successful('操作成功');

    }

}