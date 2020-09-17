<?php


namespace app\common\validate\settlement;


use think\Validate;


//入驻
class SettlementValidate extends Validate
{
    protected $rule = [
        'id|入驻id'      => 'require|number',
        'identity|身份'      => 'require|number|in:2,3,4',
        'username|账号'      => 'require',
        'password|密码'      => 'require',
        'nickname|供应商名称/店名'      => 'require',
        'contacts|联系人'      => 'require',
        'phone|联系人电话'   => ['require', 'regex' => '/^1(3|4|5|7|8)[0-9]\d{8}$/'],
        'address|地址'      => 'require',
        'business_license|营业执照'      => 'require',
        'status|状态'      => 'require',
        'examine_is|审核'      => 'require|number',
        'province|省ID'      => 'require|number',
        'city|市ID'      => 'require|number',
        'county|县区ID'      => 'require|number',
        'twon|乡镇ID'        => 'require|number',
        'idcard_img_positive|身份证正面'        => 'require',
        'idcard_img_back|身份证反面'        => 'require',
        'logo_img|店铺logo'        => 'require',
        'lng|经度'        => 'require',
        'lat|纬度'        => 'require',

    ];

    protected $message = [
            'id.require'=>'入驻id必填',
            'id.number'=>'入驻id有误',
            'identity.require'=>'身份必填',
            'identity.number'=>'身份有误',
            'username.require'=>'账号必填',
            'password.require'=>'密码必填',
            'nickname.require'=>'供应商名称/店名必填',
            'contacts.require'=>'联系人必填',
            'phone.require' => '手机必填',
            'phone.regex' => '手机格式错误',
            'address.require' => '地址必填',
            'business_license.require' => '营业执照必填',
            'status.require' => '状态必填',
            'examine_is.require' => '审核状态必填',
    ];

    protected $scene = [
            'info'=>['id'],
            'add'=>['identity','username','password','nickname','contacts','phone','province','city','county','twon','address','idcard_img_positive','idcard_img_back','logo_img','lng','lat'],
            'edit'=>['id','identity','username','nickname','contacts','phone','province','city','county','twon','address','idcard_img_positive','idcard_img_back','logo_img','lng','lat'],
            'status'=>['id','status'],
            'examine'=>['id','examine_is'],
            'admin_add'=>['identity','username','password','nickname','contacts','phone','logo_img'],

//            'user_settlement'=>['identity','username','password','nickname','contacts','phone','province','city','county','twon','address','idcard_img_positive','idcard_img_back','logo_img','lng','lat'],
    ];

    // edit 验证场景定义
    public function sceneAdminEdit()
    {
        return $this->only(['name','age'])

            ->remove('age', 'between')
            ->append('age', 'require|max:100');
    }



}