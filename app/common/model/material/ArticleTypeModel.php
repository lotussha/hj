<?php


namespace app\common\model\material;


use think\Model;
use think\model\concern\SoftDelete;
use app\common\model\CommonModel;

//文章分类模型
class ArticleTypeModel extends CommonModel
{
    protected $name = 'article_type';
//    use SoftDelete; // 一开启这个 软删除后，就没有数据了
//    protected $deleteTime = 'delete_time';


    //查询单个数据
    public function getTypeInfo($where){
            $list = $this->where($where)->field('id,name,status,img_url,sort')->find();
        if ($list){
            $list = $list->toArray();
        }
        return $list;
    }

    //查询全部数据
    public function getTypleList($where=[],$order='sort desc,id desc'){
        $list = $this
            ->field('id,name,status,img_url,sort')
            ->where('is_delete','<>','1')
            ->where($where)
            ->order($order)
            ->select();

        if ($list){
            $list = $list->toArray();
        }
        return $list;

    }

}