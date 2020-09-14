<?php


namespace app\common\logic\advertisement;

use app\common\model\advertisement\BannerPositionModel;
use sakuno\utils\JsonUtils;

//轮播图位置
class BannerPositionLogic
{
    public function Handle($data, $act = '')
    {
        $BannerPositionModel = new BannerPositionModel();
        $where = array();
        $where[] = ['name', '=', $data['name']];
        if ($act == 'edit') {
            $where[] = ['id', '<>', $data['id']];
        }

        $rs = $BannerPositionModel->where($where)->value('id');
        if ($rs) {
            return false;
//            return JsonUtils::fail('已有相同的名称', '00000');
        }
        return $data;
    }
}