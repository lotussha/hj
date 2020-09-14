<?php
/**
 * Created by PhpStorm.
 * PHP version 版本号
 *
 * @category 类别名称
 * @package  暂无
 * @author   hj <138610033@qq.com>
 * @license  暂无
 * @link     暂无
 * DateTime: 2020/8/20 下午2:40
 */

namespace app\api\controller\goods;

use app\api\controller\Api;
use app\api\logic\goods\CouponLogic;
use app\common\validate\CouponValidate;
use app\Request;
use sakuno\utils\JsonUtils;
use think\App;

class Coupon extends Api
{
    protected $couponLogic;
    public function __construct(Request $request, App $app ,CouponLogic $couponLogic)
    {
        $this->couponLogic = $couponLogic;
        parent::__construct($request, $app);
    }

    //领取优惠券
    public function get_coupon()
    {
        return $this->couponLogic->getCoupon($this->param);
    }

    //优惠券详情
    public function get_coupon_info()
    {
        return $this->couponLogic->getInfo($this->param);
    }

    //优惠券列表
    public function get_coupon_list()
    {
        $validate = new CouponValidate();
        $validate_result = $validate->scene('get_list')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        return $this->couponLogic->getList($this->param);
    }

    //修改优惠券状态
    public function state()
    {
        return $this->couponLogic->state($this->param);
    }
}