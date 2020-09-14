<?php


namespace app\common\logic\material;


use app\common\model\material\ArticleTypeModel;

//文章分类
class ArticleTypeLogic
{
    public function Handle($data, $act = '')
    {
        $ArticleTypeModel = new ArticleTypeModel();
        $where = array();
        $where[] = ['name', '=', $data['name']];
        if ($act == 'edit') {
            $where[] = ['id', '<>', $data['id']];
        }

        $rs = $ArticleTypeModel->where($where)->value('id');
        if ($rs) {
            return false;
        }
        return $data;
    }
}