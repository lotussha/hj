<?php


namespace app\common\validate\advertisement;


use think\Validate;

class BannerPositionValidate extends Validate
{
    //判断字段
    protected $rule=[
        'id|轮播图位置id'=>'require|number',
        'name|广告位置名称'=>'require',
        'width|宽度'=>'require',
        'height|高度'=>'require',
        'describe|描述'=>'require',
    ];

    //提示语
    protected $message=[
        'id.require'=>'广告位置ID必填',
        'id.number'=>'广告位置ID有误',
        'width.require'=>'宽度必填',
        'height.require'=>'高度位置必填',
        'describe.require'=>'描述必填',
    ];

    //参数
    protected $scene=[
        'add'=>['name','width','height','describe'],
        'edit'=>['id','name','width','height','describe'],
        'info'=>['id'],
        'del'=>['id'],
    ];
}