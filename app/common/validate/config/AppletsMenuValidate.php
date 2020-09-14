<?php


namespace app\common\validate\config;


use think\Validate;
//小程序菜单
class AppletsMenuValidate extends Validate
{
    //判断字段
    protected $rule=[
        'id|菜单id'=>'require',
        'name|菜单名称'=>'require',
        'img_url|图片'=>'require',
        'status|状态'=>'require',
    ];

    //提示语
    protected $message=[

    ];

    //参数
    protected $scene=[
        'info'=>['id'],
        'add'=>['name','img_url'],
        'edit'=>['id','name','img_url'],
        'status'=>['id','status'],
        'del'=>['id'],
    ];
}