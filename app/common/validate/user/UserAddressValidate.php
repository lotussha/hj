<?php


namespace app\common\validate\user;


use think\Validate;


//用户地址
class UserAddressValidate extends Validate
{
    //判断字段
    protected $rule=[
        'user_id|用户id'=>'require|number',
        'address_id|地址id'=>'require|number',
        'consignee|收货人'=>'require',
        'province|省份'=>'require',
        'city|县区'=>'require',
        'twon|乡镇'=>'require',
        'address|地址'=>'require',
        'mobile|手机'=>'require',
        'longitude|地址经度'=>'require',
        'latitude|地址纬度'=>'require',
    ];

    //提示语
    protected $message=[
    ];

    //参数
    protected $scene=[
        'list'=>['user_id'],
        'info'=>['user_id','address_id'],
        'add'=>['user_id','consignee','province','city','twon','address','mobile','longitude','latitude'],
        'edit'=>['address_id','user_id','consignee','province','city','twon','address','mobile','longitude','latitude'],
        'del'=>['user_id','address_id'],
    ];
}