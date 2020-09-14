<?php

namespace app\common\model\coupon;

use app\common\model\CommonModel;

/**
 * @mixin \think\Model
 */
class CouponIssueModel extends CommonModel
{
    protected $name = 'goods_coupon_issue';

    public $searchField = [
        'status',
        'uid'
    ];

    public $whereField = [
        'id',
        'uid',
    ];

    protected function getReceiveTextAttr($value, $data){
        $arr = [
            '1' => '新人弹窗领取',
            '2' => '分享好友领取',
            '3' => '购买满金额领取'
        ];
        return $arr[$data['receive']];
    }

    protected function getStatusTextAttr($value, $data){
        $arr = [
            '1' => '正常未使用',
            '2' => '已使用',
            '3' => '已无效',
        ];
        return $arr[$data['status']];
    }

}
