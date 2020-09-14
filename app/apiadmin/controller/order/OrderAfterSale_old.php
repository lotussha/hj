<?php


namespace app\apiadmin\controller\order;


use app\apiadmin\controller\Base;
use app\apiadmin\logic\order\OrderAfterSaleLogicOld;
use app\Request;
use sakuno\utils\JsonUtils;
use think\App;
use think\response\Json;

class OrderAfterSaleOld extends Base
{
    protected $logic;

    public function __construct(Request $request, App $app, OrderAfterSaleLogicOld $orderAfterSaleLogic)
    {
        $this->logic = $orderAfterSaleLogic;
        parent::__construct($request, $app);
    }

    /**
     * 售后订单列表
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
    public function read(Request $request)
    {
        $data = $this->logic->getInfo($request->get('id'));
        return JsonUtils::successful('获取成功', $data);
    }

    /**
     * 审核售后单
     * @param Request $request
     * @return \think\Response
     * @throws \app\exception\OrderStatusException
     */
    public function audit(Request $request)
    {
        $data = $this->logic->audit($request->post());
        return JsonUtils::successful('操作成功', $data);
    }
}