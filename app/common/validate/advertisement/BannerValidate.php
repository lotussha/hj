<?php


namespace app\common\validate\advertisement;


use think\Validate;

class BannerValidate extends Validate
{
    //判断字段
    protected $rule=[
        'id|轮播图id'=>'require|number',
        'name|广告名称'=>'require',
        'link_id|链接'=>'require',
        'position_id|广告位置ID'=>'require|number',
        'start_time|广告开始时间'=>'require',
        'end_time|广告结束时间'=>'require',
        'img_url|广告图片'=>'require',
        'background|背景图片'=>'require',
        'skip_type|分类'=>'require',
        'status|状态'=>'require|in:1,2',

    ];

    //提示语
    protected $message=[
        'id.require'=>'广告ID必填',
        'id.number'=>'广告ID有误',
        'link.require'=>'广告链接必填',
        'position_id.require'=>'广告位置必填',
        'position_id.number'=>'广告位置有误',
        'start_time.require'=>'广告开始时间有误',
        'end_time.require'=>'广告结束时间有误',
        'img_url.number'=>'广告图片必填',
        'background.number'=>'背景图片必填',
    ];


    //参数
    protected $scene=[
        'add'=>['img_url'],
        'edit'=>['id','img_url'],
        'info'=>['id'],
        'status'=>['id','status'],
        'del'=>['id'],
    ];
}