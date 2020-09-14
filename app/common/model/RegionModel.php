<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/11
 * Time: 19:53
 */

namespace app\common\model;

class RegionModel extends CommonModel
{
    protected $name = 'region';


    public function getLevelRegion($level=0,$parent_id=0)
    {
        return $this->where(['level'=>$level,'parent_id'=>$parent_id])->select();
    }
}