<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/31
 * Time: 13:36
 * 预售订单
 */

namespace app\api\controller\order;

use app\api\controller\Api;
use app\api\logic\order\OrderLogic;
use app\api\logic\order\OrderPreSaleLogic;
use app\api\validate\OrderValidate;
use app\Request;
use sakuno\utils\JsonUtils;
use think\App;

class OrderPreSale extends Api
{
    protected $user_id;
    protected $preSaleLogic;
    protected $validate;
    protected $orderLogic;
    public function __construct(Request $request, App $app)
    {
        $this->preSaleLogic = new OrderPreSaleLogic();
        $this->validate = new OrderValidate();
        $this->orderLogic = new OrderLogic();
        $this->user_id = 1;
        parent::__construct($request, $app);
    }

    public function lists()
    {
        $user_id=$this->user_id;
        $this->param['where'] = [['prom_types','=',3],['pay_status','=',2]];
        $data = $this->preSaleLogic->getList($this->param,$user_id);
        return JsonUtils::successful('获取成功',$data);
    }

    public function details()
    {
        $validate_result = $this->validate->scene('order_details')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($this->validate->getError());
        }
        $this->param['where'] = [['prom_types','=',3]];
        $data = $this->orderLogic->getDetails($this->param,$this->user_id);
        return $data;
    }
}