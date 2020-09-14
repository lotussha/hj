<?php
declare (strict_types = 1);

namespace app\apiadmin\model;

use think\facade\Db;
use think\Model;

/**
 * @mixin \think\Model
 */
class WithdrawModel extends Model
{
//    protected  $withdraw = new withdraw();
    //获取提现设置列表
    static public function getWithdrawIndex(){
        $data=Db::name("withdraw")->where("status",1)->select()->toArray();
        return $data;
    }

    //添加/更新提现信息
    static public function createWithdraw($data){
        if (!isset($data['id']) ){
            $res =Db::name("withdraw")->insertGetId($data);
        }else{
            $res = Db::name("withdraw")->save($data);
        }
        return $res;
    }

    //获取提现编辑信息回显
    static public function getMess($id){
        $data = Db::name("withdraw")->where("status",1)->order(["update_time"=>"desc","add_time"=>"desc"])->find
        ($id);
        return $data;
    }

    //更改提现按钮开启状态
    static public function isOpen($id,$is_open){
        if ($is_open == 1){
            $res = Db::name("withdraw")->where('id',$id)->update(['is_withdraw'=>2,'update_time'=>time()]);
        }else{
            $res = Db::name("withdraw")->where('id',$id)->update(['is_withdraw'=>1,'update_time'=>time()]);
        }

        return $res;
    }

    //提现设置删除
    static public function del($id){
        $res = Db::name("withdraw")->where("id",$id)->update(["status"=>2,"update_time"=>time()]);
        return $res;
    }
}
