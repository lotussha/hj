<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/26
 * Time: 10:55
 */

namespace app\common\model;

class GoodsServiceModel extends CommonModel
{
    protected $name = 'goods_service';

    public function getAddTimeDateAttr($value,$data)
    {
        return date('Y-m-d H:i:s',$data['add_time']);
    }
}