<?php


namespace app\common\validate\material;


use think\Validate;

//专题分类
class SpecialTypeValidate extends Validate
{
//判断字段
    protected $rule=[
        'id|分类id'=>'require',
        'name|分类名称'=>'require',
    ];

    //提示语
    protected $message=[
        'id.require'=>'分类ID必填',
        'name.require'=>'分类名称必填',
    ];

    //参数
    protected $scene=[
        'add'=>['name'],
        'edit'=>['id','name'],
        'info'=>['id'],
        'del'=>['id'],
    ];
}