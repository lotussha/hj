<?php


namespace app\apiadmin\controller\settlement;


use app\apiadmin\controller\Base;

//店铺轮播图
class Banner extends Base
{
    /**
     * 轮播图列表
     * User: hao
     * Date: 2020.09.05
     */
    public function index(){
        $data = $this->param;
        $data['aid'] = $this->admin_user['id'];



    }
}