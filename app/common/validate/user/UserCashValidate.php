<?php


namespace app\common\validate\user;


use think\Validate;
//提现
class UserCashValidate extends Validate
{
    //判断字段
    protected $rule=[
        'id|提现id'=>'require|number',
        'examine_is|审核状态'=>'require|in:1,2',
    ];

    //提示语
    protected $message=[
        'examine_is.in'=>'审核状态不能有误',
    ];

    //参数
    protected $scene=[
        'examine'=>['id','examine_is'],
    ];
}