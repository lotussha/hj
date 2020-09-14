<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/11
 * Time: 15:42
 */

namespace app\common\model;


class FreightTemplateModel extends CommonModel
{
    protected $name = 'freight_template';

    //可作为条件的字段
    protected $whereField = ['identity','identity_id'];

    //自定义初始化
    protected static function init()
    {
        //TODO:自定义的初始化
    }

    public function freightConfig()
    {
        return $this->hasMany('FreightConfigModel', 'template_id', 'template_id');
    }

    public function getTypeDescAttr($value, $data)
    {
        $type = config('status')['FREIGHT_TYPE'];
        return $type[$data['type']] ?? '';
    }
}