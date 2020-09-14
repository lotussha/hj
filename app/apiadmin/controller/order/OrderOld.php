<?php


namespace app\apiadmin\controller\order;

use app\apiadmin\controller\Base;
use app\apiadmin\logic\order\OrderOldLogic;
use sakuno\utils\JsonUtils;
use think\App;
use think\Request;

class OrderOld extends Base
{
    protected $logic;

    public function __construct(Request $request, App $app, OrderOldLogic $logic)
    {
        $this->logic = $logic;
//        parent::__construct($request, $app);
    }

    /**
     * 订单列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $data = $this->logic->getList(1, $request->get());
        return JsonUtils::successful('获取成功', $data);
    }



    /**
     * 订单详情
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 修改价格
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function updatePrice(Request $request)
    {
        $data = $this->logic->updateOrderAmount($request->post());
        if ($data == false) {
            return JsonUtils::fail('订单已经付款,不能再修改金额');
        }
        return JsonUtils::successful('更改成功');
    }

    /**
     * 取消订单
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function cancelOrder(Request $request)
    {
        $data = $this->logic->cancelOrder($request->post());
        return JsonUtils::successful('更改成功');
    }


    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
