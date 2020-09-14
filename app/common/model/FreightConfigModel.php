<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/11
 * Time: 19:16
 */

namespace app\common\model;


class FreightConfigModel extends CommonModel
{
    protected $name = 'freight_config';

    public function freightRegion()
    {
        return $this->hasMany('FreightRegionModel', 'config_id', 'config_id');
    }
}