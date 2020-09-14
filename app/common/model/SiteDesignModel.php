<?php
declare (strict_types = 1);

namespace app\common\model;

use think\facade\Db;
use think\Model;

/**
 * @mixin \think\Model
 */
class SiteDesignModel extends Model
{
    protected  $table = 'rh_site_base_config';

    //添加网站基本设置
    public function addSiteConfig($data){
        $res = Db::name("site_base_config")->save($data);
        return $res;
    }

    //获取网站基本设置
    static public  function getAllSiteConfig(){
        $data = Db::name("site_base_config")->where('status',1)->order("id","desc")->select()->toArray();
        return $data;
    }
    //获取网站基本设置
    static public  function getSiteConfig($id){
        $data = Db::name("site_base_config")->where('status',1)->order("id","desc")->find($id);
        return $data;
    }

    //删除网站基本设置
    static  public function del($id){
        $res = Db::name("site_base_config")->where('id',$id)->update(['status'=>2,'update_time'=>time()]);
        return $res;
    }

}
