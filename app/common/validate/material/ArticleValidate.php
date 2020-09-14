<?php


namespace app\common\validate\material;


use think\Validate;
//文章
class ArticleValidate extends Validate
{
    //判断字段
    protected $rule=[
        'id|文章id'=>'require|number',
        'title|文章标题'=>'require',
        'type_id|文章分类'=>'require',
        'content|内容'=>'require',
        'author|作者'=>'require',
    ];

    //提示语
    protected $message=[
        'id.require'=>'文章ID必填',
        'id.number'=>'文章ID输入有误',
        'title.require'=>'文章标题必填',
        'type_id.require'=>'文章分类必填',
        'content.require'=>'文章内容必填'
    ];

    //参数
    protected $scene=[
        'add'=>['title','type_id','content'],
        'edit'=>['id','title','type_id','content'],
        'info'=>['id'],
        'del'=>['id'],
    ];

}