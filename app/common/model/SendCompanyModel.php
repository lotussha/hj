<?php
declare (strict_types = 1);

namespace app\common\model;
use think\facade\Db;
use think\Model;

/**
 * @mixin \think\Model
 */
class SendCompanyModel extends Model
{
    //获取快递公司列表
    static public function getCodeIndex($data = []){
        $where = " 1=1";
        if(isset($data['com_name'])){
            $where .=" and com_name like '%{$data['com_name']}%'";
        }
        if(isset($data['com_code'])){
            $where .=" and com_code like '%{$data['com_code']}%' ";
        }
        $data=Db::name("send_company")->where("status",1)->where($where)->select()->toArray();
        return $data;
    }

    //添加/更新快递公司信息
    static public function createSendCompany($data){
        if (!isset($data['id'])){
            $res = Db::name("send_company")->insertGetId($data);
        }else{
            $res = Db::name("send_company")->save($data);
        }

        return $res;
    }

    //获取快递公司编辑信息回显
    static public function getMess($id){
        $data = Db::name("send_company")->where("status",1)->find($id);
        return $data;
    }

    //删除快递公司
    static public function del($id){
        $res = Db::name("send_company")->where('id',$id)->update(['status'=>2,'update_time'=>time()]);
        return $res;
    }

    //更改快递公司开启状态
    static public function isOpen($id,$is_open){
        if ($is_open == 1){
            $res = Db::name("send_company")->where('id',$id)->update(['is_open'=>2,'update_time'=>time()]);
        }else{
            $res = Db::name("send_company")->where('id',$id)->update(['is_open'=>1,'update_time'=>time()]);
        }

        return $res;
    }
}
