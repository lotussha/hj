<?php


namespace app\common\validate\cookbook;


use think\Validate;

class CookBookValidate extends Validate
{
//判断字段
    protected $rule=[
        'gid|菜谱ID'=>'require|number',
        'img_url|上传图片'=>'require',
        'content|评论内容'=>'require',
    ];

    //提示语
    protected $message=[
        'gid.require'=>'携带参数不能为空',
        'img_url.require'=>'请上传图片',
        'content.require'=>'内容不能为空',
    ];

    //参数
    protected $scene=[
        'ck_comment'=>['gid','content','img_url'],  //评论
        'reply_comment'=>['gid','content'],  //回复评论
        'comment_list'=>['gid'],  //评论列表

    ];
}