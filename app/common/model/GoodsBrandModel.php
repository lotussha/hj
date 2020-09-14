<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/10
 * Time: 16:24
 * 商品品牌模型
 */

namespace app\common\model;

use think\Model;

class GoodsBrandModel extends CommonModel
{
    protected $name = 'goods_brand';
    //分类
    public function goodsCategory()
    {
        return $this->hasOne('GoodsCategoryModel', 'id', 'cat_id')->field('id,name');
    }
    //可搜索字段
    protected $searchField = [
        'name',
        'cat_name',
    ];

    //可作为条件的字段
    protected $whereField = ['cat_id'];
}