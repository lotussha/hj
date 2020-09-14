<?php
declare (strict_types = 1);

namespace app\common\validate;

use think\Validate;

class CodeConfigValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'code_key'=>"require",
        'code_secret'=>"require",
        'code_name'=>"require",
        'code_delate_time'=>"require|integer",
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message = [
        'code_key.require'=>"短信平台key必填",
        'code_secret.require'=>"短信平台秘钥必填",
        'code_name.require'=>"短信模板名称必填",
        'code_delate_time.require'=>"短信超时时间必填",
        'code_delate_time.integer'=>"短信超时时间必须是整数",
    ];
}
