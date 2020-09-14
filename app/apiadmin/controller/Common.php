<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/3
 * Time: 9:33
 * 公共模块
 */

namespace app\apiadmin\controller;

use app\apiadmin\model\AdminUsers;
use app\common\logic\UploadLogic;
use app\common\model\RegionModel;
use sakuno\utils\JsonUtils;
use think\exception\ValidateException;

class Common extends Base
{
    /**
     * 获取管理员菜单列表
     * @param AdminUsers $adminUsers
     * @return \think\response\Json
     * User: Jomlz
     * Date: 2020/8/3 10:17
     */
    public function get_role_menu_list(AdminUsers $adminUsers)
    {
        $arr = array_return();
        $admin_menu = $adminUsers->getShowMenu($this->admin_user);
        $lists = getTree($admin_menu);
        $arr['data'] = arrString($lists);

        apiLog(var_export($arr, true));
        return return_json($arr);
    }

    /**
     * file文件上传
     * @return \think\response\Json
     * User: Jomlz
     * Date: 2020/8/8 17:19
     */
    public function upload_image_file()
    {
        $arr = array_return();
        $upload = new UploadLogic();
        $dir_name = $this->param['dir_name'] ?? '';
        $dir_arr = config('filesystem')['disks']['public']['dir_name'];
        if (!in_array($dir_name,$dir_arr)){
            $arr['msg'] = '地址文件名称错误';
            return return_json($arr);
        }
        $ImageInfo = [
            'main_dir'=> 'admin',
            'fide_dir'=> $dir_name,
        ];
        $res = $upload->fieldImgUpload($ImageInfo);
        if ($res['status'] == 1){
            $arr['data'] = ['image_url'=>$res['image_url']];
        }else{
            $arr['msg'] = $res['msg'];
        }
        return return_json($arr);
    }

    /**
     * base64文件上传
     * @return \think\response\Json
     * User: Jomlz
     * Date: 2020/8/10 9:47
     */
    public function upload_image_base64()
    {
        $arr = array_return();
        $upload = new UploadLogic();
        $image = $this->param['image'] ?? '';
        $dir_name = $this->param['dir_name'] ?? '';
        $dir_arr = config('filesystem')['disks']['public']['dir_name'];
        if (!in_array($dir_name,$dir_arr)){
            $arr['msg'] = '地址文件名称错误';
            return return_json($arr);
        }
        $ImageInfo = [
            'main_dir'=> 'admin',
            'fide_dir'=> $dir_name,
        ];
        $res = $upload->base64_upload($image,$ImageInfo);
        if ($res['status'] == 1){
            $arr['data'] = ['image_url'=>$res['image_url']];
        }else{
            $arr['msg'] = $res['msg'];
        }
        return return_json($arr);
    }

    //获取状态配置信息
    public function get_status_config()
    {
        $status_config = $this->param['status_config'] ?? '';
        $status = strtoupper($status_config);
        $arr = config('status')[$status];
        $data = [];
        foreach ($arr as $key=>$v){
            $data[$key]['id'] = $key;
            $data[$key]['name'] = $v;
        }
        $data = ["$status_config"=>array_values($data)];
        return JsonUtils::successful('获取成功',$data);
    }

    //根据身份ID获取用户列表
    public function get_user_by_identity(AdminUsers $adminUsers)
    {
        $identity_id = $this->param['identity_id'] ?? '';
        $lists = $adminUsers->where('identity', $identity_id)->where('status', 1)->where('delete_time', 0)->field('id,nickname')->select();
        $data['list'] = $lists;
        return JsonUtils::successful('获取成功', $data);
    }

    /**
     * 获取级别地区列表
     * @return \think\Response
     * User: Jomlz
     * Date: 2020/8/12 10:33
     */
    public function get_level_region_lists()
    {
        $level = $this->param['level'] ?? 0;
        $id = $this->param['id'] ?? 0;
        $data = (new RegionModel())->getLevelRegion($level,$id)->toArray();
        return JsonUtils::successful('获取成功',arrString($data));
    }

}