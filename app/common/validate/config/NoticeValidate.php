<?php


namespace app\common\validate\config;


use think\Validate;

//公告
class NoticeValidate extends Validate
{
    //判断字段
    protected $rule=[
        'id|公告id'=>'require',
        'title|公告标题'=>'require',
        'status|状态'=>'require',
    ];

    //提示语
    protected $message=[

    ];

    //参数
    protected $scene=[
        'info'=>['id'],
        'add'=>['title'],
        'edit'=>['id','title'],
        'status'=>['id','status'],
        'del'=>['id'],
    ];
}