<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/22
 * Time: 14:35
 */

namespace app\api\controller\order;

use app\api\controller\Api;
use app\api\logic\order\OrderLogic;
use app\api\validate\OrderValidate;
use app\common\model\cart\GoodsCartModel;
use app\Request;
use sakuno\utils\JsonUtils;
use think\App;
use think\facade\Db;

class Order extends Api
{
    protected $orderLogic;
    protected $validate;
    protected $user_id;
    public function __construct(Request $request, App $app ,OrderLogic $orderLogic,OrderValidate $validate)
    {
        $this->user_id = 1;
        $this->orderLogic = $orderLogic;
        $this->validate = $validate;
        parent::__construct($request, $app);
    }

    /**
     * 商品确认订单
     * @return \think\Response
     * User: Jomlz
     */
    public function goods_confirm_order()
    {
        $validate_result = $this->validate->scene('goods_confirm')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($this->validate->getError());
        }
        $this->param['user_id'] = $this->user_id;
        $res = $this->orderLogic->confirmOrder($this->param);
        if ($res['status'] == 1){
            return JsonUtils::successful('成功',$res['data']);
        }else{
            return JsonUtils::fail($res['msg']);
        }
    }

    /**
     * 购物车确认订单
     * @return \think\Response
     * User: Jomlz
     */
    public function cart_confirm_order()
    {
        $validate_result = $this->validate->scene('cart_confirm')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($this->validate->getError());
        }
        $this->param['user_id'] = $this->user_id;
        $res = $this->orderLogic->confirmOrder($this->param);
        if ($res['status'] == 1){
            return JsonUtils::successful('成功',$res['data']);
        }else{
            return JsonUtils::fail($res['msg']);
        }
    }

    /**
     * 商品提交订单
     * @return \think\Response
     * User: Jomlz
     */
    public function goods_add_order()
    {
        $validate_result = $this->validate->scene('goods_add_order')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($this->validate->getError());
        }
        $this->param['user_id'] = 1;
        $res = $this->orderLogic->confirmOrder($this->param);
        if ($res['status'] == 1){
            $res = $this->orderLogic->addOrder($this->param,$res['data']);
            if ($res['status'] == 1){
                return JsonUtils::successful($res['msg'],$res['data']);
            }
        }
        return JsonUtils::fail($res['msg']);
    }

    /**
     * 购物车提交订单
     * @return \think\Response
     * @throws \think\db\exception\DbException
     * User: Jomlz
     * Date: 2020/8/24 14:42
     */
    public function cart_add_order()
    {
        $validate_result = $this->validate->scene('cart_add_order')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($this->validate->getError());
        }
        $this->param['user_id'] = $this->user_id;
        $res = $this->orderLogic->confirmOrder($this->param);
        if ($res['status'] == 1){
            $res = $this->orderLogic->addOrder($this->param,$res['data'],$this->param['cart_id']);
            if ($res['status'] == 1){
                //下单清除购物车
                (new GoodsCartModel())->where([['id','in',$this->param['cart_id']]])->save(['is_del'=>1]);
                return JsonUtils::successful($res['msg'],$res['data']);
            }
        }
        return JsonUtils::fail($res['msg']);
    }

    /**
     * 我的订单
     * User: Jomlz
     */
    public function my_order()
    {
        $user_id=$this->user_id;
        $this->param['where'] = [['prom_types','not in',[1,3]]];
        $data = $this->orderLogic->getOrder($this->param,$user_id);
        return JsonUtils::successful('获取成功',$data);
    }

    public function order_goods_info()
    {
        $validate_result = $this->validate->scene('order_goods')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($this->validate->getError());
        }
        $user_id=$this->user_id;
        $data = $this->orderLogic->getOrderGoods($this->param,$user_id);
        return $data;
    }

    /**
     * 订单详情
     * @return \think\Response
     * User: Jomlz
     */
    public function order_details()
    {
        $validate_result = $this->validate->scene('order_details')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($this->validate->getError());
        }
        $user_id=$this->user_id;
        $this->param['where'] = [['prom_types','not in',[3]]];
        $data = $this->orderLogic->getDetails($this->param,$user_id);
        return $data;
    }

    /**
     * 取消订单
     * User: Jomlz
     */
    public function cancel_order()
    {
        $validate_result = $this->validate->scene('order_details')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($this->validate->getError());
        }
        $user_id=$this->user_id;
        $this->param['where'] = ['pay_status'=>0];
        $this->param['data'] = ['order_status'=>3];
        $res = $this->orderLogic->orderSave($this->param,$user_id);
        return $res;
    }

    /**
     * 删除订单
     * User: Jomlz
     */
    public function del_order()
    {
        $validate_result = $this->validate->scene('order_details')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($this->validate->getError());
        }
        $user_id=$this->user_id;
        $this->param['where'] = ['pay_status'=>0,'order_status'=>3];
        $this->param['data'] = ['is_del'=>1];
        $res = $this->orderLogic->orderSave($this->param,$user_id);
        return $res;
    }

    /**
     * 确认收货
     * User: Jomlz
     */
    public function confirm_receipt()
    {
        $validate_result = $this->validate->scene('order_details')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($this->validate->getError());
        }
        $user_id=$this->user_id;
        $this->param['where'] = [['shipping_status','=',1],['order_status','<>',2],['confirm_time','=',0]];
        $this->param['data'] = ['order_status'=>2,'confirm_time'=>time()];
        $res = $this->orderLogic->orderSave($this->param,$user_id);
        return $res;
    }

    /**
     * 物流信息
     */
    public function logistics_info()
    {
        $validate_result = $this->validate->scene('order_details')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($this->validate->getError());
        }
        $user_id=$this->user_id;
        $res = $this->orderLogic->logisticsInfo($this->param,$user_id);
        return $res;
    }

}