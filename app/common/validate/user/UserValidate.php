<?php


namespace app\common\validate\user;


use think\Validate;

class UserValidate extends Validate
{
    //判断字段
    protected $rule=[
        'id|用户id'=>'require|number',
        'is_touch|手动/自动生成'=>'require',
        'integral|积分'=>'require|^[1-9]\d*$',
        'commission|佣金金额'=>'require|^[1-9]\d*$',
        'recharge|充值金额'=>'require|^[1-9]\d*$',
    ];

    //提示语
    protected $message=[
        'is_touch.require'=>'选择生成方式',
        'integral.^[1-9]\d*$'=>'积分要正整数',
        'commission.^[1-9]\d*$'=>'佣金金额要正整数',
        'recharge.^[1-9]\d*$'=>'充值金额要正整数',
    ];

    //参数
    protected $scene=[
        'add'=>['is_touch'],
        'info'=>['id'],
        'integral'=>['id','integral'],
        'commission'=>['id','commission'],
        'recharge'=>['id','recharge'],
    ];


}