<?php
declare (strict_types = 1);

namespace app\common\validate;

use think\Validate;

class WebConfigValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    "site_record"=>"require",
	    "site_name"=>"require",
	    "site_logo"=>"require",
	    "site_user_logo"=>"require",
	    "site_title_logo"=>"require",
	    "site_control_logo"=>"require",
	    "site_admin_top_logo"=>"require",
	    "site_phone_index_logo"=>"require",
	    "site_phone_login_logo"=>"require",
	    "site_desc"=>"require",
	    "site_keywords"=>"require",
	    "site_connector"=>"require",
	    "site_mobile"=>"require|mobile",
//	    "site_phone"=>"",
	    "region"=>"require",
	    "address"=>"require",
	    "site_person1"=>"require",
//	    "site_person2"=>"",
//	    "site_person3"=>"",
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        "site_record.require"=>"网站备案号必填",
        "site_name.require"=>"网站名称必填",
        "site_logo.require"=>"网站logo必须上传",
        "site_user_logo.require"=>"用户中心logo必传",
        "site_title_logo.require"=>"网站标题图标必传",
        "site_control_logo.require"=>"平台管理员登录页必传",
        "site_admin_top_logo.require"=>"后台顶部logo必传",
        "site_phone_index_logo.require"=>"手机端首页logo必传",
        "site_phone_login_logo.require"=>"手机端登录页必传",
        "site_desc.require"=>"网站描述必填",
        "site_keywords.require"=>"网站关键字必填",
        "site_connector.require"=>"网站联系人必填",
        "site_mobile.require"=>"网站联系电话必填",
        "site_mobile.mobile"=>"电话号码格式不正确",
//        "site_phone"=>"",
//        "site_phone.phone"=>"座机号格式不正确",
//        "site_phone.require"=>"网站座机号必填",
        "region.require"=>"区域必填",
        "address.require"=>"地址必填",
        "site_person1.require"=>"联系人必填",
//        "site_person2.require"=>"",
//        "site_person3.require"=>"",
    ];
}
