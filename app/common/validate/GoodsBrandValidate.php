<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/10
 * Time: 19:52
 */

namespace app\common\validate;

use think\facade\Db;
use think\Validate;

class GoodsBrandValidate extends Validate
{
    protected $rule = [
        'id|id'      => 'require|checkGoods',
        'name|品牌名称'      => 'require|unique:goods_brand',
        'cat_id|分类id'       => 'require|checkCatId',
        'logo|品牌logo'       => 'require',
        'desc|品牌描述'       => 'require',
    ];

    protected $message = [
        'id.require'  => 'ID不能为空',
    ];

    protected $scene = [
        'add'   => ['cat_id','name'],
        'del'   => ['id'],
    ];

    //edit验证场景定义
    public function sceneEdit()
    {
        return $this->only(['id','cat_id','name'])->remove('id','checkGoods');
    }
    //info验证场景定义
    public function sceneInfo(){
        return $this->only(['id'])->remove('id','checkGoods');
    }

    protected function checkCatId($value,$rule,$data)
    {
        $cat = Db::name('goods_category')->where(['id'=>$data['cat_id']])->find();
        if(!$cat){
            return '分类不存在';
        }else{
            return true;
        }
    }

    protected function checkGoods($value,$rule,$data)
    {
        $goods = Db::name('goods')->where(['brand_id'=>$data['id']])->find();
        if($goods){
            return '该品牌下有商品不能删除';
        }else{
            return true;
        }
    }
}