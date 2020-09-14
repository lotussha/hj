<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/4
 * Time: 17:03
 */

namespace app\common\model;

use think\Model;

class GoodsAttributeModel extends Model
{
    protected $name = 'goods_attribute';

    public function goodsType()
    {
        return $this->hasOne('GoodsTypeModel','id','type_id');
    }
}