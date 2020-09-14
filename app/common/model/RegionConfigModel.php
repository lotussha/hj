<?php
declare (strict_types = 1);

namespace app\common\model;

use app\JsonUtils;
use think\facade\Db;
use think\Model;

/**
 * @mixin \think\Model
 */
class RegionConfigModel extends Model
{
    protected $table = "rh_region";
    protected $fields = ["id","level","parent_id","area_code","city_code","name","merger_name","lnt","lat","status","is_send"];

    //获取可送货地区列表
    static public function getRegionIndex($data = []){
        $where = " status=1 and is_send=1";
        if(isset($data['region_name'])){
            $where .=" and name like '%{$data['region_name']}%'";
        }
        if (isset($data['id']) && $data['id']!=0){
            $where .=" and parent_id = {$data['id']} ";
        }else{
            unset($data['id']);
        }
//        halt($where);
        $data=Db::name("region")
            ->field("id,parent_id,level,area_code,city_code,name,merger_name,lng,lat,status,is_send")
            ->where($where)
//            ->fetchSql(true)
            ->select()
            ->toArray();
        return $data;
    }



    //添加/更新可配送区域信息
    static public function createSendArea($data){
        if (!isset($data['id']) ){
            $data['id'] = 0;
        }
        $region = Db::name("region")
            ->where("name like  '%{$data['region_name']}%'")
            ->where("parent_id",$data['id'])
            ->where("is_send",1)
           ->count();
        if ($region>0){
            $res = 0;
        }else {
            $res = Db::name("region")
                ->where("name like  '%{$data['region_name']}%'")
                ->where("parent_id",$data['id'])
                ->update(['update_time' => time(), "is_send" => 1]);
        }
        return $res;
    }

//获取区域链
    static public function getRegionName($id){
        static  $str;
        $data = Db::name("region")->where("id",$id)->field("name,parent_id")->find();
        if ($data['parent_id'] ==0){
           $str []= $data['name'];
        }else{
            $str []= $data['name'];
            self::getRegionName($data['parent_id']);
        }
        return $str;
    }


    //返回上级可配送区域菜单
    static public function getUpperList($level){
        $data = Db::name("region")->where(["level"=>$level,"is_send"=>1])->field("name,parent_id,level,id")
            ->select()->toArray();
//        halt(Db::getLastSql());
        return $data;
    }

    //删除快递公司
    static public function del($id){
        $res = Db::name("region")->where('id',$id)->update(['is_send'=>2,'update_time'=>time()]);
        return $res;
    }


}
