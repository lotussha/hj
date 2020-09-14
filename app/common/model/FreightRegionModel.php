<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/11
 * Time: 19:31
 */

namespace app\common\model;


class FreightRegionModel extends CommonModel
{
    protected $name = 'freight_region';

    public function region()
    {
        return $this->hasOne('RegionModel', 'id', 'region_id')->field('id,name');
    }

    public function freightConfig()
    {
        return $this->hasOne('FreightConfigModel', 'config_id', 'config_id');
    }
}