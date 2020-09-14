<?php


namespace app\common\logic\advertisement;


class BannerLogic
{
    public function Handle($data){

        if (isset($data['start_time'])){
            $data['start_time'] = strtotime($data['start_time']);
        }
        if (isset($data['end_time'])){
            $data['end_time'] = strtotime($data['end_time']);
        }
        return $data;
    }
}