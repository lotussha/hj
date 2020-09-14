<?php


namespace app\common\validate\order;


use think\Validate;

class OrderCommentValidate extends Validate
{
    //判断字段
    protected $rule=[
        'id|评论id'=>'require|number',
        'gid|商品id'=>'require|number',
        'content|内容'=>'require',
    ];

    //提示语
    protected $message=[
        'id.require'=>'评论ID必填',
        'id.number'=>'评论ID有误',
        'gid.require'=>'商品ID必填',
        'gid.number'=>'商品ID有误',
    ];

    //参数
    protected $scene=[
        'add'=>['gid','content'],
        'edit'=>['id','content'],
        'info'=>['id'],
        'del'=>['id'],
        'reply'=>['id','content'],
    ];
}