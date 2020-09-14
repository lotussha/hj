<?php


namespace app\common\model\advertisement;


use app\common\model\CommonModel;
use think\Model;
use think\model\concern\SoftDelete;
//轮播图位置
class BannerPositionModel extends CommonModel
{
//    use SoftDelete; // 一开启这个 软删除后，就没有数据了
//    protected $deleteTime = 'delete_time';
    protected $name = 'banner_position';
    //可搜索字段
    protected $searchField = [
        'name'
    ];
    //查询单个数据
    public function getPositionInfo($where,$field){
        $list = $this->where($where)->field($field)->find();
        if ($list){
            $list = $list->toArray();
        }
        return $list;
    }

    //查询全部数据
    public function getPositionList($receive){
        $receive['list_rows'] = isset($receive['list_rows'])?$receive['list_rows']:10;  //多少条
        $receive['field'] = isset($receive['field'])?$receive['field']:'';//指定字段
        $list = $this
            ->where('is_delete','<>',1)
            ->field($receive['field'])
            ->scope('where', $receive)
            ->paginate($receive['list_rows']);
        return $list->toArray();
    }

}