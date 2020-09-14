<?php


namespace app\common\validate\material;


use think\Validate;

//消息
class NewsValidate extends Validate
{
    //判断字段
    protected $rule=[
        'id|消息id'=>'require|number',
        'title|消息标题'=>'require',
        'content|内容'=>'require',
        'is_show|是否显示'=>'require',
    ];

    //提示语
    protected $message=[
        'title.require'=>'消息标题必填',
        'content.require'=>'文章内容必填'
    ];

    //参数
    protected $scene=[
        'add'=>['title','is_show'],
        'edit'=>['id','title','is_show'],
        'info'=>['id'],
        'del'=>['id'],
    ];
}