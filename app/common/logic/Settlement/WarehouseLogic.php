<?php


namespace app\common\logic\settlement;


use app\common\model\settlement\WarehouseModel;

class WarehouseLogic
{
    public function Handle($data, $act = '')
    {
        $WarehouseModel = new WarehouseModel();
        $where = array();
        $where[] = ['username', '=', $data['username']];
        if ($act == 'edit') {
            $where[] = ['id', '<>', $data['id']];
        }

        $rs = $WarehouseModel->where($where)->value('id');
        if ($rs) {
            return false;
        }

        if (isset($data['password'])){
            $data['password'] = password_hash($data['password'], 1);
        }

        return $data;
    }
}