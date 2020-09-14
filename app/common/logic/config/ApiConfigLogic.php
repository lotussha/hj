<?php


namespace app\common\logic\config;


use app\common\model\advertisement\BannerModel;
use sakuno\utils\JsonUtils;

class ApiConfigLogic
{
    /**
     * 轮播图
     * User: hao
     * Date: 2020.9.3
     */
    public function banner($receive){
        $position_id = $receive['position_id']??0;
        $now = time();
        $where = array();
        $where[] =['position_id','=',$position_id];
        $where[] = ['status','=','1'];
        $where[] = ['is_delete','=','0'];
//        $where[] = ['end_time','<',$now];
//        $where[] = ['start_time','>',$now];
        $list = (new BannerModel())->getList($where,'id,name,type,link_id,position_id,start_time,end_time,img_url,img_url,skip_type');
        return JsonUtils::successful('操作成功',['list'=>$list]);

    }
}