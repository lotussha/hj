<?php

namespace app\api\controller\config;
use app\api\controller\Api;
use app\common\logic\config\ApiConfigLogic;
use think\App;
use think\Request;

class Config extends Api
{
    public function __construct(Request $request, App $app)
    {
        parent::__construct($request, $app);
    }

    /**
     * 轮播图
     * User: hao  2020-8-29
     */
    public function banner(){
        $data = $this->param;
        $logic = new ApiConfigLogic();
        $res = $logic->banner($data);
        return $res;
    }
}