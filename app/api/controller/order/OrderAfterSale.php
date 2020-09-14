<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/25
 * Time: 9:59
 */

namespace app\api\controller\order;

use app\api\controller\Api;
use app\api\logic\order\OrderAfterSaleLogic;
use app\common\validate\OrderAftersalesValidate;
use app\Request;
use sakuno\utils\JsonUtils;
use think\App;

class OrderAfterSale extends Api
{
    protected $orderAfterSaleLogic;
    protected $validate;
    protected $user_id;
    public function __construct(Request $request, App $app ,OrderAfterSaleLogic $orderAfterSaleLogic,OrderAftersalesValidate $validate)
    {
        $this->orderAfterSaleLogic = $orderAfterSaleLogic;
        $this->validate = $validate;
        $this->user_id = 1;
        parent::__construct($request, $app);
    }

    /**
     * 售后列表
     * @return \think\Response
     * User: Jomlz
     */
    public function lists()
    {
        $this->param['orderGoodsWhere'] = [['prom_type','not in',[3]]];
        $data = $this->orderAfterSaleLogic->getOrderAfterSale($this->param,$this->user_id);
        return JsonUtils::successful('获取成功',$data);
    }

    /**
     * 申请售后
     * User: Jomlz
     */
    public function apply()
    {
        $validate_result = $this->validate->scene('apply')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($this->validate->getError());
        }
        $res = $this->orderAfterSaleLogic->addAfterSale($this->param,$this->user_id);
        return $res;
    }

    /**
     * 获取售后详情
     * User: Jomlz
     */
    public function details()
    {
        $validate_result = $this->validate->scene('info')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($this->validate->getError());
        }
        $res = $this->orderAfterSaleLogic->getDetails($this->param,$this->user_id);
        return $res;
    }

    /**
     * 用户发货
     */
    public function user_delivery()
    {
        $validate_result = $this->validate->scene('user_delivery')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($this->validate->getError());
        }
        $res = $this->orderAfterSaleLogic->userDelivery($this->param,$this->user_id);
        return $res;
    }

    /**
     * 取消售后服务
     * User: Jomlz
     */
    public function cancel()
    {
        $validate_result = $this->validate->scene('cancel')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($this->validate->getError());
        }
        $res = $this->orderAfterSaleLogic->cancel($this->param,$this->user_id);
        return $res;
    }

    /**
     * 换货商品确认收货
     * User: Jomlz
     */
}