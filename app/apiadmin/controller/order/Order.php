<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/14
 * Time: 14:14
 * 订单管理
 */

namespace app\apiadmin\controller\order;

use app\apiadmin\controller\Base;
use app\apiadmin\logic\order\OrderLogic;
use app\Request;
use sakuno\utils\JsonUtils;
use think\App;

class Order extends Base
{
    protected $orderLogic;

    public function __construct(Request $request, App $app , OrderLogic $orderLogic)
    {
        $this->orderLogic = $orderLogic;
        parent::__construct($request, $app);
    }

    /**
     * 订单列表
     * @return \think\Response
     * User: Jomlz
     */
    public function lists()
    {
        $data = $this->orderLogic->getList($this->param);
        return JsonUtils::successful('获取成功', $data);
    }

    /**
     * 订单详情
     * @return \think\Response
     * User: Jomlz
     */
    public function order_detail()
    {
        $res = $this->orderLogic->getOrderDetail($this->param);
        if ($res['status'] == 0){
            return JsonUtils::fail($res['msg']);
        }
        return JsonUtils::successful($res['msg'], $res['data']);
    }

    /**
     * 调整订单商品价格
     * @return \think\Response
     * User: Jomlz
     */
    public function readjust_price()
    {
        $data = $this->orderLogic->readjustOrderGoodsPrice($this->param);
        return $data;
    }

    /**
     * 订单操作
     * @return \think\Response
     * User: Jomlz
     */
    public function order_action()
    {
        $res = $this->orderLogic->orderProcessHandle($this->param);
        return $res;
    }

    /**
     * 发货单列表
     * User: Jomlz
     */
    public function delivery_lists()
    {
        $shipping_status = $this->param['shipping_status'] ?? 0; //默认未发货
        $this->param['where'] = [
            ['shipping_status','=',$shipping_status],
            ['order_status','in',[1,2,4]],
        ];
        $data = $this->orderLogic->getList($this->param);
        return JsonUtils::successful('获取成功', $data);
    }

    /**
     * 发货单详情
     * @return \think\Response
     * User: Jomlz
     * Date: 2020/8/18 20:38
     */
    public function delivery_detail()
    {
        $res = $this->orderLogic->getDeliveryDetail($this->param);
        if ($res['status'] == 0){
            return JsonUtils::fail($res['msg']);
        }
        return JsonUtils::successful($res['msg'], $res['data']);
    }

    /**
     * 订单发货
     * @return \think\Response
     * User: Jomlz
     */
    public function delever_handle()
    {
        $res = $this->orderLogic->deleverHandle($this->param);
        if ($res['status'] == 0){
            return JsonUtils::fail($res['msg']);
        }
        return JsonUtils::successful($res['msg']);
    }

}