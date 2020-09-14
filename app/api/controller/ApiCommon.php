<?php


namespace app\api\controller;


use app\api\logic\ApiCommonLogic;
use app\api\logic\pay\PayLogic;
use app\common\logic\GetCodeLogic;
use app\common\logic\UploadLogic;
use app\common\model\RegionModel;
use app\common\validate\ApiCommonValidate;
use sakuno\utils\JsonUtils;
use think\App;
use think\Request;
use WeChatApplets\WeChatPayment;

class ApiCommon extends Api
{
    protected $validate;
    protected $logic;
    protected $needAuth = false;
    public function __construct(Request $request, App $app)
    {
        $this->validate = new ApiCommonValidate();
        $this->logic = new ApiCommonLogic();
        parent::__construct($request, $app);
    }

    /**
     * 获取级别地区列表
     * @return \think\Response
     * User: hao
     * Date: 2020/8/24
     */
    public function get_level_region_lists()
    {
        $level = $this->param['level'] ?? 0;
        $id = $this->param['id'] ?? 0;
        $data = (new RegionModel())->getLevelRegion($level,$id)->toArray();
        return JsonUtils::successful('获取成功',arrString($data));
    }
    /**
     * file文件上传
     * @return \think\response\Json
     * User: hao
     * Date: 2020/8/24
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
            'main_dir'=> 'api',
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
     * User: hao
     * Date: 2020/8/24
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
            'main_dir'=> 'api',
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


    /**
     * 获取验证码
     * @return \think\response\Json
     * $phone 手机号
     * $type  1：小程序  2：管理后台
     * $scenes_id 1:修改密码  2：修改支付密码
     * User: hao
     * Date: 2020.09.08
     */
    public function get_code(){
        $data = $this->param;
        $logic = new GetCodeLogic();
        $res = $logic->get_code($data);
        return $res;
    }
}