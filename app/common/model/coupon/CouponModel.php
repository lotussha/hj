<?php

namespace app\common\model\coupon;

use app\common\model\CommonModel;
use sakuno\utils\JsonUtils;
use think\Model;

/**
 * @mixin \think\Model
 */
class CouponModel extends CommonModel
{
    protected $name = 'goods_coupon';

    //可搜索字段
    public $searchField = [
        'title',
        'category_name'
    ];

    public $receive_text = [
        '1' => '新人弹窗领取',
        '2' => '分享好友领取',
        '3' => '购买满金额领取'
    ];

    public $status_text = [
        '1' => '正常未使用',
        '2' => '已使用',
        '3' => '已无效',
    ];

    public function getInfo($param)
    {
        $info = self::findInfo(['id' => $param['id']]);
        $info['receive'] = $this->receive_text[$info['receive']];
        $info['status'] = $this->status_text[$info['status']];
        $info['add_time'] = date('Y-m-d h:i:s', $info['add_time']);
        return JsonUtils::successful('获取成功', $info);
    }
}
