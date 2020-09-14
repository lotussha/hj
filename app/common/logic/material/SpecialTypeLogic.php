<?php


namespace app\common\logic\material;


use app\common\model\material\SpecialTypeModel;

//专题分类
class SpecialTypeLogic
{
    public function Handle($data, $act = '')
    {
        $SpecialTypeModel = new SpecialTypeModel();
        $where = array();
        $where[] = ['name', '=', $data['name']];
        if ($act == 'edit') {
            $where[] = ['id', '<>', $data['id']];
        }

        $rs = $SpecialTypeModel->where($where)->value('id');
        if ($rs) {
            return false;
        }
        return $data;
    }
}