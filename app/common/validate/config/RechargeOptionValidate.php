<?php


namespace app\common\validate\config;


use think\Validate;
//充值选项
class RechargeOptionValidate extends Validate
{
    //判断字段
    protected $rule=[
        'id|选项id'=>'require',
        'min_money|最低金额'=>'require',
        'max_money|最高金额'=>'require',
        'give|赠送金额'=>'require',
        'status|状态'=>'require',
    ];

    //提示语
    protected $message=[

    ];

    //参数
    protected $scene=[
        'info'=>['id'],
        'add'=>['min_money','max_money','give'],
        'edit'=>['id','min_money','max_money','give'],
        'status'=>['id','status'],
        'del'=>['id'],
    ];
}