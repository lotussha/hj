<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/3
 * Time: 19:28
 */

namespace app\apiadmin\validate;

use think\facade\Db;
use think\Validate;

class GoodsCategoryValidate extends Validate
{
    protected $rule = [
        'id|商品分类id'      => 'require|number|gt:0|checkCatId',
        'name|商品分类名称'      => 'require|sameCate',
        'parent_id|商品上级分类id'   => 'require|sameCate',
        'sort' => 'number',
        'is_show' => 'number',
        'image' => 'require',
    ];

    protected $message = [
    ];

    protected $scene = [
        'add'   => [ 'name'],
        'edit'  => ['id.require','name','parent_id'],
        'info' => ['id.require'],
        'del' => [ 'id'],
    ];

    protected function sameCate($value,$rule,$data)
    {
        if ($data['parent_id'] > 0){
            $cat = Db::name('goods_category')->where(['id'=>$data['parent_id'],'is_del'=>0])->find();
            if(!$cat){
                return '分类不存在';
            }
            if ($cat['level'] == 3){
                return '最多只能三级';
            }
        }
        if (isset($data['id'])){
            if ($data['id'] === $data['parent_id']){
                return '上级分类不能为自己';
            }
        }
        $id = $data['id'] ?? 0;
        $sameCateWhere = ['parent_id'=>$data['parent_id'], 'name'=>$data['name'],'is_del'=>0];
        $cat = Db::name('goods_category')->where($sameCateWhere)->where('id','<>',$id)->find();
        if($cat){
            return '同级已有相同分类存在';
        }else{
            return true;
        }

    }

    protected function checkCatId($value,$rule,$data)
    {
        $ids = $data['id'];
        // 判断子分类
//        $count = Db::name("goods_category")->where("parent_id = {$ids}")->count("id");
        $count = Db::name("goods_category")->where([['parent_id','in',[$ids]],['is_del','=',0]])->count("id");
        if ($count > 0){
            return '该分类下还有分类不得删除';
        };
        // 判断是否存在商品
        $goods_count = Db::name('goods')->where("cat_id = {$ids}")->count();
        if ($goods_count > 0){
            return '该分类下有商品不得删除';
        }
        // 判断是否存在品牌
        $brand_count = Db::name('goods_brand')->where("cat_id = {$ids}")->count();
        if ($brand_count > 0){
            return '该分类下有品牌不得删除';
        }
        return true;
    }
}