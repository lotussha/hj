<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/4
 * Time: 18:07
 */

namespace app\apiadmin\controller\goods;

use app\apiadmin\controller\Base;
use app\apiadmin\logic\GoodsLogic;
use app\common\model\GoodsBrandModel;
use app\common\model\GoodsCategoryModel;
use app\common\model\GoodsImagesModel;
use app\common\model\GoodsModel;
use app\common\model\GoodsSpecModel;
use app\common\model\GoodsTypeModel;
use app\common\validate\GoodsBrandValidate;
use app\common\validate\GoodsValidate;
use sakuno\utils\JsonUtils;

class Goods extends Base
{
    /**
     * 商品列表
     * User: Jomlz
     * Date: 2020/8/10 16:22
     */
    public function lists(GoodsLogic $goodsLogic)
    {
        $this->param['field'] = 'goods_id,cat_id,goods_sn,goods_name,brand_id,shop_price,store_count,warehouse_id,warehouse_id,is_recommend,sort,identity,identity_id,cost_price,let_profits_price,is_on_sale';
        $page = isset($this->param['page']) ? $this->param['page'] : 1;
        $list_rows = $this->param['list_rows'] ?? $this->admin['list_rows'];
        $lists = $goodsLogic->getGoodsList($page,$list_rows,$this->param);
        return JsonUtils::successful('获取成功',$lists);
    }

    /**
     * 商品信息
     * User: Jomlz
     * Date: 2020/8/10 16:22
     */
    public function info(GoodsModel $goodsModel,GoodsValidate $validate,GoodsTypeModel $goodsTypeModel,GoodsImagesModel $imagesModel)
    {
        $validate_result = $validate->scene('info')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $hidden = ['extend_cat_id','price_ladder','is_virtual','virtual_indate','virtual_limit','virtual_refund','on_time',
            'sales_sum','last_update','spu','sku','add_time','is_del','check_remark','is_check','update_time','goods_score','service_ids','prom_type','prom_id','video'];
        //商品信息
        $info = $goodsModel
            ->scope('where',$this->param)
            ->where(['goods_id'=>$this->param['goods_id'],'is_del'=>0])
            ->append(['service_arr'])
            ->hidden($hidden)
            ->find();
        if (!$info){
            return JsonUtils::fail('信息不存在');
        }
        $goods_images = $imagesModel->getGoodsImg($this->param['goods_id']);     //商品多图
        $response['info'] = turnString($info->toArray());
        $response['goods_images'] = $goods_images;
        return JsonUtils::successful('获取成功',$response);
    }

    /**
     * 编辑字段
     * User: Jomlz
     */
    public function edit_field(GoodsModel $goodsModel,GoodsValidate $validate)
    {
        $validate_result = $validate->scene('field')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $info = $goodsModel->scope('where',$this->param)->where(['goods_id'=>$this->param['goods_id']])->find();   //商品信息
        if (!$info){
            return JsonUtils::fail('信息不存在');
        }
        $field = $this->param['field'];
        $value = $this->param['value'];
        $checkField = ['is_new','is_on_sale','sort','is_recommend','is_hot','is_del'];
        if (!in_array($field,$checkField)){
            return JsonUtils::fail('该字段不可编辑');
        }
        $res = $goodsModel->setFieldValue(['goods_id'=>$this->param['goods_id']],$field,$value);
        if ($res){
            return JsonUtils::successful('编辑成功');
        }else{
            return JsonUtils::fail('编辑失败');
        }
    }

    /**
     * 获取商品模型
     * User: Jomlz
     * Date: 2020/8/5 16:43
     */
    public function goods_spec_items(GoodsSpecModel $goodsSpecModel)
    {
        if (!isset($this->param['type_id']) && !isset($this->param['goods_id'])){
            return JsonUtils::fail('传参错误');
        }
        $res = $goodsSpecModel->getGoodsSpecItems($this->param);
        return JsonUtils::successful('获取成功',$res);
    }

    public function add_goods()
    {
        return $this->goodsHandle('add');
    }

    public function edit_goods()
    {
        return $this->goodsHandle('edit');
    }

    /**
     * 删除商品
     * User: Jomlz
     * Date: 2020/8/10 16:22
     */
    public function del_goods(GoodsLogic $goodsLogic)
    {
        $goods_ids = $this->param['goods_id'] ?? 0;
        if (empty($goods_ids)){
            return JsonUtils::fail( 'goods_id不能为空');
        };
        return $goodsLogic->delGoods($goods_ids);;
    }

    /**
     * @商品操作
     * User: Jomlz
     * Date: 2020/8/10 16:22
     */
    public function goodsHandle($act='')
    {
        $goodsLogic = new GoodsLogic();
        $validate = new GoodsValidate;
        $validate_result = $validate->scene($act)->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $arr = $goodsLogic->addEditGoodsInfo($this->param,$this->admin_user['id']);
        return return_json($arr);
    }

    /**
     * 品牌列表
     * User: Jomlz
     * Date: 2020/8/10 19:44
     */
    public function brand_lists(GoodsLogic $goodsLogic)
    {
        $this->param['list_rows'] = $this->param['list_rows'] ?? $this->admin['list_rows'];
        $lists = $goodsLogic->BrandList($this->param);
        $data = ['lists'=>$lists];
        return JsonUtils::successful('获取成功',$data);
    }

    /**
     * 获取全部品牌
     * User: Jomlz
     * Date: 2020/8/11 10:36
     */
    public function get_brand_lists(GoodsBrandModel $goodsBrandModel)
    {
        $lists = $goodsBrandModel->getList(['is_del'=>0]);
        $data['lists'] = arrString($lists);
        return JsonUtils::successful('获取成功',$data);
    }

    /**
     * 品牌信息
     * User: Jomlz
     * Date: 2020/8/11 10:39
     */
    public function brand_info(GoodsBrandModel $model,GoodsBrandValidate $validate,GoodsCategoryModel $goodsCategoryModel)
    {
        $validate_result = $validate->scene('info')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $info = $model::find($this->param['id']);
        if (!$info){
            return JsonUtils::fail('信息不存在');
        }
        $response['info'] = turnString($info->toArray());
        return JsonUtils::successful('获取成功',$response);
    }

    public function brand_add(GoodsLogic $goodsLogic)
    {
        return $goodsLogic->brandHandle('add',$this->param);
    }

    public function brand_edit(GoodsLogic $goodsLogic)
    {
        return $goodsLogic->brandHandle('edit',$this->param);
    }
    public function brand_del(GoodsLogic $goodsLogic)
    {
        return $goodsLogic->brandHandle('del',$this->param);
    }

}