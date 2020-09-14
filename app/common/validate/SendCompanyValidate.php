<?php
declare (strict_types = 1);

namespace app\common\validate;

use think\Validate;

class SendCompanyValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'com_name'=>"require",
	    'com_code'=>"require"
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        "com_name.require"=>"快递公司名称必填",
        "com_code.require"=>"快递公司代号必填",
    ];
}
