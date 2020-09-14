<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/8
 * Time: 9:55
 * 上传
 */

namespace app\common\logic;

use think\exception\ValidateException;

class UploadLogic
{
    /**
     * @param array $ImageInfo
     * @return array
     * User: Jomlz
     * Date: 2020/8/8 13:27
     */
    public function fieldImgUpload($ImageInfo=array())
    {
        $main_dir = $ImageInfo['main_dir'] ?? 'admin' ; //保存主文件夹名称
        $fide_dir = $ImageInfo['fide_dir'] ?? 'common'; //保存副文件夹名称
        $img_path = $main_dir . '/' .$fide_dir;
        $configUrl = config('filesystem')['disks']['public']['url'];
        $key = array_keys($_FILES)[0];
        $file = request()->file($key);
        try{
            //验证上传文件
            validate([$key=>[
                //限制文件大小
                'fileSize' => 3 * 1024 * 1024,
                'fileExt' => 'jpg,png,jpeg,gif',
            ]],[
                "$key.fileSize" => '上传的文件大小不能超过3M',
                "$key.fileExt" => '请上传后缀为:jpg,jpeg,png,gif的文件'
                ])->check(request()->file());
            $savename = \think\facade\Filesystem::disk('public')->putFile($img_path, $file);
            $image_url = $configUrl.'/' .str_replace('\\', '/', $savename);
        }catch (ValidateException $e){
            return ['status'=>0,'msg'=>$e->getMessage()];
        }
        return ['status'=>1,'msg'=>'成功','image_url'=>$image_url];
    }

    /**
     * BASE64形式图片上传
     * @param $base64  图片数据
     * @param array $ImageInfo
     * @return array
     * User: Jomlz
     * Date: 2020/8/8 17:53
     */
    public function base64_upload($base64,$ImageInfo=array()) {
        $main_dir = $ImageInfo['main_dir'] ?? 'admin' ; //保存主文件夹名称
        $fide_dir = $ImageInfo['fide_dir'] ?? 'common'; //保存副文件夹名称

        $base64_image = str_replace(' ', '+', $base64);
        //post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)){
            $image_name = uniqid().'.'.$result[2];
            $configUrl = config('filesystem')['disks']['public']['url'];
            $file = '.'.$configUrl .'/'.$main_dir . '/' . $fide_dir . '/' .date('Ymd') . "/";
            !is_dir($file) && @mkdir($file, 0777, true); //创建文件夹
            $image_file = $file . '/' . $image_name;
            //服务器文件存储路径
            if (file_put_contents($image_file, base64_decode(str_replace($result[1], '', $base64_image)))){
                $image_url = ltrim($image_file,'.');
                return ['status'=>1,'msg'=>'成功','image_url'=>$image_url];
            }else{
                return ['status'=>0,'msg'=>'上传失败'];
            }
        }else{
            return ['status'=>0,'msg'=>'文件格式错误'];
        }
    }
}