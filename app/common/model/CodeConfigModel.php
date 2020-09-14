<?php
declare (strict_types = 1);

namespace app\common\model;

use think\facade\Db;
use think\Model;

/**
 * @mixin \think\Model
 */
class CodeConfigModel extends Model
{
    //获取短信基本设置列表
    public function getCodeConfigIndex(){
        $res = Db::name("code_config")->where("status",1)->select()->toArray();
        return $res;
    }

    //创建短信基本设置
    static public  function createCodeConfig($data){
        if (isset($data['id'])){
            $data = Db::name("code_config")->save($data);
        }else{
            $data = self::insertGetId($data);
        }

        return $data;
    }

    //获取网站基本设置返显
    static public  function getCodeConfig($id){
        $data = Db::name("code_config")->where("status",1)->find($id);
        return $data;
    }



    //删除短信基本设置
    static  public function del($id){
        $res = Db::name("code_config")->where("id",$id)->update(["status"=>2,"update_time"=>time()]);
        return $res;
    }
}
