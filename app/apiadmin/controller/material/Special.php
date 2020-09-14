<?php
declare (strict_types = 1);

namespace app\apiadmin\controller\material;

use app\apiadmin\controller\Base;
use app\common\logic\material\SpecialTypeLogic;
use app\common\model\material\SpecialModel;
use app\common\model\material\SpecialTypeModel;
use app\common\validate\material\SpecialTypeValidate;
use app\common\validate\material\SpecialValidate;
use sakuno\utils\JsonUtils;
use think\Collection;

//专题
class Special extends Base
{
    /**
     * 专题列表
     * User: hao
     * Date: 2020/8/8
     */
    public function index(SpecialModel $SpecialModel){
        $page = isset($this->param['page'])?$this->param['page']:1;
        $where = array();
        if (isset($this->param['type_id'])){
            $where['type_id'] = $this->param['type_id'];
        }
        $lists = $SpecialModel->getAllSpecial($page,10,$where);
        return JsonUtils::successful('获取成功',$lists);
    }

    /**
     * 专题详情
     * User: hao
     * Date: 2020/8/8
     */
    public function special_info(SpecialModel $SpecialModel){

        //检验
        $validate = new SpecialValidate;
        $validate_result = $validate->scene('info')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),00000);
        }

        //路由
        $lists = $SpecialModel->getInfoSpecial(['id'=>$this->param['id']]);
        return JsonUtils::successful('获取成功',$lists);
    }

    /**
     * 专题添加
     * User: hao
     * Date: 2020/8/8
     */
    public function special_add(SpecialModel $SpecialModel){
        $data = $this->param;
        //检查
        $validate = new SpecialValidate();
        $validate_result = $validate->scene('add')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),00000);
        }
        //模型
        $arr =$SpecialModel->addInfo($data);
        if ($arr){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败',00000);
        }
    }

    /**
     * 专题添加
     * User: hao
     * Date: 2020/8/8
     */
    public function special_edit(SpecialModel $SpecialModel){
        $data = $this->param;
        //检查
        $validate = new SpecialValidate();
        $validate_result = $validate->scene('edit')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),00000);
        }
        //模型
        $arr =$SpecialModel->updateInfo(['id'=>$this->param['id']],$data);
        if ($arr){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败',00000);
        }
    }

    /**
     * 专题删除
     * User: hao
     * Date: 2020/8/8
     */
    public function special_del(SpecialModel $SpecialModel){
        //检验
        $validate = new SpecialValidate;
        $validate_result = $validate->scene('del')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),00000);

        }
        //路由
        $lists = $SpecialModel->deleteInfo(['id'=>$this->param['id']]);
        if (!$lists){
            return JsonUtils::fail('操作失败',00000);
        }
        return JsonUtils::successful('操作成功');
    }

    /**
     * 专题分类列表
     * User: hao
     * Date: 2020/8/8
     */
    public function type_list(SpecialTypeModel $SpecialTypeModel){

        $page = isset($this->param['page'])?$this->param['page']:1;
        $where = array();
        $lists = $SpecialTypeModel->getTypleList($page,10,$where);
        return JsonUtils::successful('获取成功',$lists);

    }

    /**
     * 专题分类详情
     * User: hao
     * Date: 2020/8/8
     */
    public function type_info(SpecialTypeModel $SpecialTypeModel){

        //检验
        $validate = new SpecialTypeValidate;
        $validate_result = $validate->scene('info')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),00000);
        }

        //路由
        $lists = $SpecialTypeModel->getTypeInfo(['id'=>$this->param['id']]);
        return JsonUtils::successful('获取成功',$lists);
    }

    /**
     * 专题添加
     * User: hao
     * Date: 2020/8/8
     */
    public function type_add(SpecialTypeModel $SpecialTypeModel){

        $data = $this->param;
        //检验
        $validate = new SpecialTypeValidate();
        $validate_result = $validate->scene('add')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),00000);
        }

        //过滤相同名称
        $logic = new SpecialTypeLogic();
        $data = $logic->Handle($data);
        if (!$data){
            return JsonUtils::fail('已有相同的名称',00000);
        }

        //模型
        $arr =$SpecialTypeModel->addInfo($data);

        if ($arr){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败',00000);
        }
    }

    /**
     * 专题修改
     * User: hao
     * Date: 2020/8/8
     */
    public function type_edit(SpecialTypeModel $SpecialTypeModel){
        $data = $this->param;
        //检验
        $validate = new SpecialTypeValidate();
        $validate_result = $validate->scene('edit')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),00000);
        }

        //过滤相同名称
        $logic = new SpecialTypeLogic();
        $data = $logic->Handle($data,'edit');
        if (!$data){
            return JsonUtils::fail('已有相同的名称',00000);
        }

        //模型
        $arr =$SpecialTypeModel->updateInfo(['id'=>$data['id']],$data);

        if ($arr){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败',00000);
        }
    }

    /**
     * 专题分类删除
     * User: hao
     * Date: 2020/8/8
     */
    public function type_del(SpecialTypeModel $SpecialTypeModel){
        //检验
        $validate = new SpecialTypeValidate;
        $validate_result = $validate->scene('del')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),00000);
        }
        //路由
        $lists = $SpecialTypeModel->deleteInfo(['id'=>$this->param['id']]);
        if (!$lists){
            return JsonUtils::fail('操作失败',00000);
        }
        return JsonUtils::successful('操作成功');
    }
}
