<?php
declare (strict_types = 1);

namespace app\common\validate;

use think\Validate;

class SiteDesignValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'site_register_integral'=>"require",
        'site_invite_integral'=>"require",
        'vip_price'=>"require",
        'vip_back_price'=>0,
        'upload_size'=>"require|integer",
        'goods_default_num'=>"require|integer|min:0",
        'goods_warn_num'=>"require|integer|min:0",
        'search_keywords'=>"require",
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message = [
        'site_register_integral.require'=>"注册送积分不可为空",
        'site_invite_integral.require'=>"邀请注册送积分不可为空",
        'vip_price.require'=>"vip充值价格不能为空",
        'vip_back_price.require'=>"",
        'upload_size.require'=>"附件上传限制不能为空",
        'upload_size.integer'=>"附件上传大小必须是整数",
        'goods_default_num.require'=>"默认库存数不能为空",
        'goods_default_num.integer'=>"默认库存数必须是整数",
        'goods_default_num.min'=>"默认库存数不能小于0",
        'goods_warn_num.require'=>"库存预警数不能为空",
        'goods_warn_num.integer'=>"库存预警数必须是整数",
        'goods_warn_num.min'=>"库存预警数不能小于0",
        'search_keywords.require'=>"搜索关键字不能为空",
    ];
}
