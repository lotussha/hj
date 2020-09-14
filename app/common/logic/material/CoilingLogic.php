<?php


namespace app\common\logic\material;

//一键发圈处理
class CoilingLogic
{
    //处理
    public function handle($data){

        if (isset($data['img_url'])){
            $img_url = isImg($data['img_url']);
            if (!$img_url){
                return ['data_code'=>false,'data_msg'=>'图片格式不正确'];
            }
            $img_url = explode(',',$img_url);
            if (count($img_url)>8){
                return ['data_code'=>false,'data_msg'=>'图片长度不能超过8张'];
            }
        }
        return $data;
    }
}