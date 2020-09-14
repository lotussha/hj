<?php

namespace app\common\validate;

use think\Validate;

class CouponValidate extends Validate
{
    protected $rule = [
        'id|优惠券ID'                    => 'require',
        'title|优惠券名称'                 => 'require',
        'category_id|分类ID'            => 'require|number',
        'category_name|分类名称'          => 'require',
        'goods_id|商品ID'               => 'requireWithout:goods_category_id|number',
        'goods_category_id|商品分类ID'      => 'requireWithout:goods_id|number',
        'coupon_price|优惠券面额'          => 'require|float',
        'use_min_price|使用条件(金额)'      => 'require|float',
        'coupon_time|有效期限(天)'         => 'require|integer',
        'sort|排序'                     => 'number',
        'status|状态'                   => 'require|integer',
        'restrictions|使用限制(数量)'       => 'require|number',
        'receive|领取类型'                => 'require|integer',
        'receive_additional|领取类型附加条件' => 'require',
    ];

    protected $scene = [
        'add'  => ['title', 'category_id', 'category_name', 'goods_id', 'coupon_price', 'use_min_price', 'restrictions', 'coupon_time', 'sort', 'status', 'receive', 'receive_additional'],
        'edit' => ['id', 'title', 'category_id', 'category_name', 'goods_id', 'coupon_price', 'use_min_price', 'restrictions', 'coupon_time', 'sort', 'status', 'receive', 'receive_additional'],
        'info' => ['id'],
        'get_list' => ['goods_id', 'goods_category_id']
    ];
}