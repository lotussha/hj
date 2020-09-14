<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/4
 * Time: 18:14
 */

namespace app\common\model;

use app\common\logic\HandleLogic;
use app\common\model\settlement\SettlementModel;
use think\facade\Db;
use think\Model;

class CookBookModel extends CommonModel
{
    protected $name = 'markering_menu';

    //可搜索字段
    protected $searchField = [
        'id',
        'cate_id',
        'menu_title',
    ];
    //可作为条件的字段
    protected $whereField = [
        'is_delete',
        'is_recommend',
    ];

    //菜谱分类
    public function goodsCategory()
    {
        //return $this->hasOne('GoodsCategoryModel', 'id', 'cat_id')->field('id,name');
    }


}