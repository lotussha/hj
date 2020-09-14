<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/21
 * Time: 16:45
 */

namespace app\api\controller\cart;

use app\api\controller\Api;
use app\api\logic\cart\CartLogic;
use app\common\validate\cart\CartValidate;
use app\Request;
use sakuno\utils\JsonUtils;
use think\App;

class Cart extends Api
{
    protected $cartLogic;
    protected $validate;
    public function __construct(Request $request, App $app , CartLogic $cartLogic , CartValidate $validate)
    {
        $this->cartLogic = $cartLogic;
        $this->validate = $validate;
        parent::__construct($request, $app);
    }

    /**
     * 购物车列表
     * @return \think\Response
     * User: Jomlz
     */
    public function index()
    {
        $this->param['user_id'] = 1;
        $res = $this->cartLogic->getCartList($this->param);
        $arr['car_lists'] = $res;
        return JsonUtils::successful('获取成功',$arr);
    }

    /**
     * 加入购物车
     * @return \think\Response
     * User: Jomlz
     */
    public function add_cart()
    {
        $validate_result = $this->validate->scene('add')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($this->validate->getError());
        }
        $this->param['user_id'] = 1;
        $res = $this->cartLogic->addCart($this->param);
        return $res;
    }

    /**
     * 修改购物车数量
     * @return \think\Response
     * User: Jomlz
     */
    public function change_num()
    {
        $validate_result = $this->validate->scene('change_num')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($this->validate->getError());
        }
        $this->param['user_id'] = 1;
        $this->param['act'] = 'change_num';
        $res = $this->cartLogic->cartHandle($this->param);
        return $res;
    }

    /**
     * 修改选中状态
     * @return \think\Response
     * User: Jomlz
     */
    public function change_selected()
    {
        $validate_result = $this->validate->scene('change_selected')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($this->validate->getError());
        }
        $this->param['user_id'] = 1;
        $this->param['act'] = 'change_selected';
        $res = $this->cartLogic->cartHandle($this->param);
        return $res;
    }

    /**
     * 删除购物车
     * @return \think\Response
     * User: Jomlz
     */
    public function delete()
    {
        $validate_result = $this->validate->scene('del')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($this->validate->getError());
        }
        $this->param['user_id'] = 1;
        $this->param['act'] = 'del';
        $res = $this->cartLogic->cartHandle($this->param);
        return $res;
    }
}