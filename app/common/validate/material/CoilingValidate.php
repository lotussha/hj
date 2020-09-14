<?php


namespace app\common\validate\material;


use think\Validate;
//一键发圈
class CoilingValidate extends Validate
{
//判断字段
    protected $rule=[
        'id|发圈id'=>'require|number',
        'gid|商品id'=>'require',
        'copywriting|文案'=>'require',
    ];

    //提示语
    protected $message=[
        'id.require'=>'发圈ID必填',
        'id.number'=>'发圈ID输入有误',
        'gid.require'=>'商品id必填',
        'copywriting.require'=>'文案必填',
    ];

    //参数
    protected $scene=[
        'add'=>['gid','copywriting'],
        'edit'=>['id','gid','copywriting'],
        'info'=>['id'],
        'del'=>['id'],
    ];
}