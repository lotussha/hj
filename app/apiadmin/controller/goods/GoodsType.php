<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/4
 * Time: 10:42
 * 商品模型
 */

namespace app\apiadmin\controller\goods;

use app\apiadmin\controller\Base;
use app\common\logic\HandleLogic;
use app\common\model\GoodsAttributeModel;
use app\common\model\GoodsSpecItemModel;
use app\common\model\GoodsSpecModel;
use app\common\model\GoodsTypeModel;
use app\common\validate\GoodsAttributeValidate;
use app\common\validate\GoodsSpecValidate;
use app\common\validate\GoodsTypeValidate;
use sakuno\utils\JsonUtils;

class GoodsType extends Base
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        if ($this->admin_user['identity'] != 1){
            echo json_encode(['status'=>'0','code'=>"10000",'msg'=>'没权限']);die;
        }
    }
    /**商品模型
     * User: Jomlz
     * Date: 2020/8/4 14:40
     */
    public function type_lists(GoodsTypeModel $model)
    {
        $lists = $model->scope('where',$this->param)->where(['is_del'=>0])->paginate($this->admin['list_rows'])->toArray();
        return JsonUtils::successful('获取成功',$lists);
    }

    /**
     * 获取商品模型列表
     * User: Jomlz
     * Date: 2020/8/10 20:46
     */
    public function get_goods_type_lists(GoodsTypeModel $goodsTypeModel)
    {
        $goods_type = $goodsTypeModel->getAllGoodsType();
        $lists = ['type_list'=>$goods_type];
        return JsonUtils::successful('获取成功',$lists);
    }

    public function type_info(GoodsTypeModel $model,GoodsTypeValidate $validate){
        $arr = array_return();
        $validate_result = $validate->scene('info')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $info = $model->getTypeInfo($this->param['id']);
        if (!$info){
            return JsonUtils::fail('信息不存在');
        }
        $data = ['info'=>$info->toArray()];
        return JsonUtils::successful('获取成功',$data);
    }

    public function type_add(HandleLogic $handleLogic)
    {
        return $this->type_handle('add');
    }
    public function type_edit()
    {
        return $this->type_handle('edit');
    }
    public function type_del()
    {
        return $this->type_handle('del');
    }

    /**
     * 模型操作
     * @param string $act
     * @return \think\response\Json
     * User: Jomlz
     * Date: 2020/8/4 17:47
     */
    public function type_handle($act='')
    {
        $arr = array_return();
        $handleLogic = new HandleLogic;
        $validate = new GoodsTypeValidate;
        $validate_result = $validate->scene($act)->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        if ($act == 'del'){
            $this->param['is_del'] = 1;
            $act = 'edit';
        }
        $arr = $handleLogic->Handle($this->param,'GoodsTypeModel',$act);
        return return_json($arr);
    }

    /**
     * 商品模型规格列表
     * User: Jomlz
     * Date: 2020/8/4 14:41
     */
    public function spec_list(GoodsSpecModel $model,GoodsSpecValidate $validate)
    {
        $validate_result = $validate->scene('list')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $list_rows = $this->param['list_rows'] ?? 10;
        $list = $model->with(['goodsSpecItem'=>function($query){
            $query->hidden(['is_del','spec_id']);
        }])
            ->where(['type_id'=>$this->param['type_id'],'is_del'=>0])->hidden(['type_id','is_del'])->paginate($list_rows)->toArray();
        return JsonUtils::successful('获取成功',$list);
    }

    public function spec_item_info(GoodsSpecModel $model,GoodsSpecValidate $validate)
    {
        $validate_result = $validate->scene('info')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $info = $model->getSpecItemInfo($this->param['id']);
        if (!$info){
            return JsonUtils::fail('信息不存在');
        }
        $data = ['info'=>$info];
        return JsonUtils::successful('获取成功',$data);
    }

    public function spec_item_add()
    {
        return $this->spec_item_hand('add');
    }

    public function spec_item_edit()
    {
        return $this->spec_item_hand('edit');
    }

    public function spec_item_del()
    {
        return $this->spec_item_hand('del');
    }

    public function spec_item_hand($act='')
    {
        $arr = array_return();
        $handleLogic = new HandleLogic;
        $goodsSpecItemModel = new GoodsSpecItemModel;
        $validate = new GoodsSpecValidate;

        $validate_result = $validate->scene($act)->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $new_act = $act;
        if ($act == 'del'){
            $new_act = 'del';
            $this->param['is_del'] = 1;
            $act = 'edit';
        }
        $arr = $handleLogic->Handle($this->param,'GoodsSpecModel',$act);
        if ($arr['status'] == 1){
            $this->param['spec_id'] = $arr['object_id'];
            if ($new_act == 'del'){
//                $goodsSpecItemModel->where("spec_id=".$this->param['id'])->delete(); //删除规格项
            }else{
                $goodsSpecItemModel->afterSave($this->param);
            }
        }
        error:
        return return_json($arr);
    }

    /**
     * 模型属性列表
     * User: Jomlz
     * Date: 2020/8/4 17:22
     */
    public function attribute_list(GoodsAttributeModel $attribute,GoodsAttributeValidate $validate)
    {
        $validate_result = $validate->scene('list')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
//        $list = $attribute->with('goodsType')->where("type_id=".$this->param['type_id'])->paginate()->toArray();
        $list_rows = $this->param['list_rows'] ?? 10;
        $list = $attribute->where("type_id=".$this->param['type_id'])->paginate($list_rows)->toArray();
        return JsonUtils::successful('获取成功',$list);
    }

    public function attribute_info(GoodsAttributeModel $attribute,GoodsAttributeValidate $validate)
    {
        $validate_result = $validate->scene('info')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $info = $attribute->where(['attr_id'=>$this->param['attr_id']])->find();
        if (!$info){
            return JsonUtils::fail('信息不存在');
        }
        $data = ['info'=>$info];
        return JsonUtils::successful('获取成功',$data);
    }

    public function attribute_add()
    {
        return $this->attribute_handle('add');
    }

    public function attribute_edit()
    {
        return $this->attribute_handle('edit');
    }

    public function attribute_del()
    {
        return $this->attribute_handle('del');
    }

    /**
     * 模型属性操作
     * User: Jomlz
     * Date: 2020/8/4 17:46
     */
    public function attribute_handle($act='')
    {
        $arr = array_return();
        $handleLogic = new HandleLogic;
        $model = new GoodsAttributeModel;
        $validate = new GoodsAttributeValidate;

        $validate_result = $validate->scene($act)->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
//        dump($this->param);die;
//        if ($act == 'del'){
//            $this->param['is_del'] = 1;
//            $act = 'edit';
//        }
        $arr = $handleLogic->Handle($this->param,'GoodsAttributeModel',$act,'attr_id');
        error:
        return return_json($arr);
    }
}