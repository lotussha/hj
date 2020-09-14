<?php


namespace app\common\validate\User;


use think\Validate;

class UserGradeValidate extends Validate
{
//判断字段
    protected $rule=[
        'id|等级id'=>'require|number',
        'name|等级名称'=>'require',
        'cash_lowest|提现最低金额'=>'require|float',
        'cash_upper|提现上限'=>'require|float',
        'cash_charge|提现手续费'=>'require|float',
        'discount|商品折扣价'=>'require|float',
        'status|状态'=>'require|number',
    ];

    //提示语
    protected $message=[
    ];

    //参数
    protected $scene=[
        'info'=>['id'],
        'add'=>['name','cash_lowest','cash_upper','cash_charge','discount'],
        'edit'=>['id','name','cash_lowest','cash_upper','cash_charge','discount'],
        'status'=>['id','status'],
    ];


}