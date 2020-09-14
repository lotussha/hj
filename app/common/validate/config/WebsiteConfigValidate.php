<?php


namespace app\common\validate\config;


use think\Validate;
//网站配置
class WebsiteConfigValidate extends Validate
{
    //判断字段
    protected $rule=[
        'config_type|配置分类'=>'require',
        'val|内容'=>'require',
    ];

    //提示语
    protected $message=[

    ];

    //参数
    protected $scene=[
        'index'=>['config_type'],
        'edit'=>['config_type','val'],
    ];

}