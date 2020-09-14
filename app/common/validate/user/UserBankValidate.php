<?php


namespace app\common\validate\user;


use think\Validate;

//用户银行卡
class UserBankValidate extends Validate
{
    //判断字段
    protected $rule=[
        'id|银行卡id'=>'require|number',
        'uid|用户id'=>'require|number',
        'name|持卡人姓名'=>'require',
        'card|卡号'=>'require',
        'issuing_bank|发卡行'=>'require',
        'phone|预留手机号'=>'require',
        'idcard|身份证'=>'require',
    ];

    //提示语
    protected $message=[
        'uid.require'=>'用户id不能为空',
    ];

    //参数
    protected $scene=[
        'list'=>['uid'],
        'info'=>['uid','id'],
        'add'=>['uid','name','card','issuing_bank','phone','idcard'],
        'edit'=>['id','uid','name','card','issuing_bank','phone','idcard'],
        'del'=>['id','uid'],
    ];
}