<?php


namespace app\common\validate\material;


use think\Validate;

//专题
class SpecialValidate extends Validate
{
//判断字段
    protected $rule=[
        'id|专题id'=>'require',
        'title|专题标题'=>'require',
        'type_id|专题分类'=>'require',
        'content|内容'=>'require',
        'cover|封面'=>'require',
    ];

    //提示语
    protected $message=[
        'id.require'=>'专题ID必填',
        'title.require'=>'专题标题必填',
        'type_id.require'=>'专题分类必填',
        'content.require'=>'专题内容必填',
        'cover.require'=>'专题封面必填'
    ];

    //参数
    protected $scene=[
        'add'=>['title','type_id','content','cover'],
        'edit'=>['id','title','type_id','content','cover'],
        'info'=>['id'],
        'del'=>['id'],
    ];
}