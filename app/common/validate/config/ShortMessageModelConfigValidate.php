<?php


namespace app\common\validate\config;


use think\Validate;

//短信模板
class ShortMessageModelConfigValidate extends Validate
{

    //判断字段
    protected $rule=[
        'id|场景id'=>'require',
        'scene_id|应用场景id'=>'require|number',
        'autograph|短信签名'=>'require',
        'message_content|短信内容'=>'require',
    ];

    //提示语
    protected $message=[

    ];

    //参数
    protected $scene=[
        'info'=>['id'],
        'add'=>['scene_id','autograph','message_content'],
        'edit'=>['id','scene_id','autograph','message_content'],
        'del'=>['id']
    ];
}