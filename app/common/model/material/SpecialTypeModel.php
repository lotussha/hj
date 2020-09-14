<?php


namespace app\common\model\material;


use think\Model;
use think\model\concern\SoftDelete;
use app\common\model\CommonModel;

class SpecialTypeModel extends CommonModel
{
    protected $name = 'special_type';
//    use SoftDelete; // 一开启这个 软删除后，就没有数据了
//    protected $deleteTime = 'delete_time';


    //查询单个数据
    public function getTypeInfo($where){
        $list = $this->where($where)->field('id,name,status,sort')->find();
        if ($list){
            $list = $list->toArray();
        }
        return $list;
    }

    //查询全部数据
    public function getTypleList($page=1,$list_rows=10,$where=[],$order='sort desc,id desc'){
        $list = $this
            ->field('id,name,status,sort')
            ->where($where)
            ->where('is_delete','<>',1)
            ->page($page,$list_rows)
            ->order($order)
            ->select();
        if ($list){
            $list = $list->toArray();
        }

        return $list;

    }
    //假删除数据
//    public function typeDel($where){
//        $res = $this->where($where)->update(['status'=>3,'delete_time'=>time()]);
//        return $res;
//    }
}