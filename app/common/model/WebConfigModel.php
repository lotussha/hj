<?php
declare (strict_types = 1);

namespace app\common\model;

use think\facade\Db;
use think\Model;

/**
 * @mixin \think\Model
 */
class WebConfigModel extends Model
{
    protected $table = 'rh_site_base_config';
    //添加网站基本设置
    public function addWebSiteConfig($data){
        if(isset($data['id'])){
            $res = Db::name("site_config")->save($data);
        }else{
            $res = Db::name("site_config")->insertGetId($data);
        }

        return $res;
    }

    //获取网站基本设置
    static public  function getSiteConfig(){
        $data = Db::name("site_config")->where('status',1)->order("id","desc")->find(1);
        return $data;
    }

    //获取网站基本设置返显
    static public  function getNowSiteConfig($id){
        $data = Db::name("site_config")->find($id);
        return $data;
    }

    //删除网站基本设置
    static  public function del($id){
        $res = Db::name("site_config")->where('id',$id)->update(['status'=>2,'update_time'=>time()]);
        return $res;
    }
}
