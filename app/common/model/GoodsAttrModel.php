<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/5
 * Time: 16:25
 */

namespace app\common\model;

use think\Model;

class GoodsAttrModel extends Model
{
    protected $name = 'goods_attr';

    protected function attribute()
    {
        return $this->hasOne(GoodsAttributeModel::class,'attr_id','attr_id')->field('attr_id,attr_name');
    }
}