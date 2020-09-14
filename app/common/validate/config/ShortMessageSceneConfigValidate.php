<?php


namespace app\common\validate\config;


use think\Validate;
//短信场景
class ShortMessageSceneConfigValidate extends Validate
{
    //判断字段
    protected $rule=[
        'id|场景id'=>'require',
        'name|场景名称'=>'require',
    ];

    //提示语
    protected $message=[

    ];

    //参数
    protected $scene=[
        'add'=>['name'],
        'edit'=>['id','name'],
        'del'=>['id'],
    ];
}