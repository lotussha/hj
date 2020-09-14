<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/4
 * Time: 10:44
 */

namespace app\common\model;

class GoodsTypeModel extends CommonModel
{
    protected $name = 'goods_type';

    //可搜索字段
    protected $searchField = [
        'name',
    ];

    public function getTypeInfo($id=0)
    {
        return $this->where(['is_del'=>0])->find($id);

    }

    public function getAllGoodsType()
    {
        return $this->where(['is_del'=>0])->select()->toArray();
    }
}