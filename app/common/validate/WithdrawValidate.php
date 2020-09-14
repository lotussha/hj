<?php
declare (strict_types = 1);

namespace app\common\validate;

use think\Validate;

class WithdrawValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    "withdraw_time"=>"require|integer|min:0",
	    "withdraw_rate"=>"require|min:0.00",
	    "withdraw_low_rate"=>"require|min:0.00",
	    "withdraw_high_rate"=>"require|min:0.00",
	    "withdraw_low_num"=>"require|min:0.00",
	    "withdraw_high_num"=>"require|min:0.00",
	    "withdraw_dayly_money"=>"require|min:0.00",
	    "withdraw_dayly_num"=>"require|integer|min:0",

    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        "withdraw_time.require"=>"可提现时间必填",
        "withdraw_time.integer"=>"可提现时间为整数",
        "withdraw_time.min"=>"可提现时间不小0",
        "withdraw_rate.require"=>"提现费率必填",
        "withdraw_low_rate.require"=>"提现最低手续费必填",
        "withdraw_high_rate.require"=>"提现最高手续费必填",
        "withdraw_low_num.require"=>"提现最低金额必填",
        "withdraw_high_num.require"=>"提现最高金额必填",
        "withdraw_dayly_money.require"=>"每日提现金额必填",
        "withdraw_dayly_num.require"=>"每日提现次数必填",
        "withdraw_rate.min"=>"提现费率不小于0",
        "withdraw_low_rate.min"=>"提现最低手续费不小于0",
        "withdraw_high_rate.min"=>"提现最高手续费不小于0",
        "withdraw_low_num.min"=>"提现最低金额不小于0",
        "withdraw_high_num.min"=>"提现最高金额不小于0",
        "withdraw_dayly_money.min"=>"每日提现金额不小于0",
        "withdraw_dayly_num.min"=>"每日提现次数不小于0",
        "withdraw_dayly_num.integer"=>"每日提现次数必须是整数",

    ];
}
