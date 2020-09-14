<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/17
 * Time: 19:29
 */

namespace app\apiadmin\controller\order;

use app\apiadmin\controller\Base;
use app\apiadmin\logic\order\OrderAfterSaleLogic;
use app\Request;
use sakuno\utils\JsonUtils;
use think\App;

class OrderAfterSale extends Base
{
    protected $orderAfterSaleLogic;
    public function __construct(Request $request, App $app , OrderAfterSaleLogic $orderAfterSaleLogic)
    {
        $this->orderAfterSaleLogic = $orderAfterSaleLogic;
        parent::__construct($request, $app);
    }

    /**
     * 售后列表
     * @return \think\Response
     * User: Jomlz
     */
    public function lists()
    {
        $data = $this->orderAfterSaleLogic->getList($this->param);
        return JsonUtils::successful('获取成功', $data);
    }

    /**
     * 售后详情
     * @return \think\Response
     * User: Jomlz
     */
    public function detail()
    {
        $data = $this->orderAfterSaleLogic->getDetail($this->param);
        return $data;
    }

    /**
     * 售后操作
     * User: Jomlz
     */
    public function handle()
    {
        $data = $this->orderAfterSaleLogic->handle($this->param);
        return $data;
    }
}