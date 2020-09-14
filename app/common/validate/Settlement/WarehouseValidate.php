<?php


namespace app\common\validate\settlement;

//仓库
use think\Validate;

class WarehouseValidate extends Validate
{
    //判断字段
    protected $rule=[
        'id|仓库id'=>'require|number',
        'nickname|仓库名称'=>'require',
        'username|账号'=>'require',
        'password|密码'=>'require',
        'address|地址'=>'require',
        'contacts|联系人'=>'require',
        'phone|联系人电话'=>'require',
        'status|状态'=>'require|number',

    ];

    //提示语
    protected $message=[
        'id.require'=>'仓库ID必填',
        'id.number'=>'仓库ID有误',
        'username.require'=>'账号必填',
        'nickname.require'=>'仓库名称必填',
        'password.require'=>'密码必填',
        'address.require'=>'地址必填',
        'contacts.require'=>'联系人必填',
        'phone.require'=>'联系人电话必填',
        'status.require'=>'状态必填',
    ];

    //参数
    protected $scene=[
        'info'=>['id'],
        'add'=>['username','nickname','password','address','contacts','phone'],
        'edit'=>['id','username','nickname','password','address','contacts','phone'],
        'status'=>['id','status'],
    ];
}