<?php


namespace app\common\validate\config;


use think\Validate;

//短信接口
class ShortMessageInterfaceConfigValidate extends Validate
{

    //判断字段
    protected $rule=[
        'id|接口id'=>'require',
        'appkey|appkey'=>'require',
        'secretkey|secretkey'=>'require',
        'name|名称'=>'require',
    ];

    //提示语
    protected $message=[

    ];

    //参数
    protected $scene=[
        'info'=>['id'],
        'add'=>['appkey','secretkey','name'],
        'edit'=>['id','appkey','secretkey','name'],
        'del'=>['id'],
    ];
}