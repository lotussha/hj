<?php


namespace app\api\controller\admin;


use app\api\controller\ApiAdmin;
use app\api\logic\admin\AdminDetailsLogic;
use app\Request;
use think\App;
//店铺详情
class AdminDetails extends ApiAdmin
{
    public function __construct(Request $request, App $app)
    {
        parent::__construct($request, $app);
    }


    /**
     * 店铺中心
     * User: hao  2020.09.05
     */
    public function shop_center(){
        $data = $this->param;
        $data['aid'] = $this->admin_id;
//        echo 1;
        $logic = new AdminDetailsLogic();
        $res = $logic->shop_center($data);
        return $res;
    }
}