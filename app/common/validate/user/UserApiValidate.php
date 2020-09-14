<?php


namespace app\common\validate\user;


use think\Validate;

class UserApiValidate extends Validate
{
//判断字段
    protected $rule=[
        'wx_code|微信小程序登录的code'=>'require',
        'encryptedData|微信小程序用户基本信息的en'=>'require',
        'iv|微信小程序用户基本信息的iv'=>'require',
        'phone_encryptedData|微信小程序手机号授权的en'=>'require',
        'phone_iv|微信小程序手机号授权的iv'=>'require',
        'nick_name|用户昵称'=>'require',
        'avatar_url|用户头像'=>'require',
        'path|跳转路径'=>'require',
        'urlParam|携带参数'=>'require',
        'money|金额'=>'require|regex:\d{1,10}(\.\d{1,2})?$',
        'cash_type|提现类型'=>'require|between:1,3',
        'openid|小程序openid'=>'require',
        'username|账号'=>'require',
        'password|密码'=>'require',
        'code|验证码'=>'require',
        'phone|手机号'=>'require',
        'code_type|验证码类型'=>'require|between:1,3',
        'integral_type|积分明细'=>'require|in:1,2,3',
        'commission_status|佣金明细'=>'require|in:1,2',
        'cash_mode|提现方式'=>'require|in:1,2',
        'goods_id|商品id'=>'require|number',
        'order_id|订单id'=>'require|number',
        'content|内容'=>'require',
        'rec_id|商品订单id'=>'require',
        'collect_pid|收藏的id'=>'require',
        'collect_type|收藏分类'=>'require|in:1,2,3',
        'collect_is|是否收藏'=>'require|in:1,2',
        'goods_collect_is|好物圈分类'=>'require|in:1,2',
        'team_level|用户团队层级'=>'require|in:0,1,2',
        'scenes_id|场景id'=>'require',
    ];

    //提示语
    protected $message=[
        'path.require'=>'跳转路径不能为空',
        'urlParam.require'=>'携带参数不能为空',
        'cash_type.between'=>'提现类型有误',
        'code_type.between'=>'验证码类型有误',
        'integral_type.between'=>'积分类型有误',
        'commission_status.in'=>'佣金类型有误',
        'cash_mode.in'=>'提现方式有误',
    ];

    //参数
    protected $scene=[
        'login'=>['wx_code','nick_name','avatar_url'],  //登录
        'bind'=>['openid','nick_name','avatar_url','username','code','password',],  //登录
        'share'=>['path','urlParam'],  //分享
        'recharge'=>['money'],  //充值
        'cash'=>['money','cash_type','cash_mode'],//提现
        'code'=>['phone','code_type'],//获取验证码
        'integral_detail'=>['integral_type'],//积分明细
        'commission_detail'=>['commission_status'],//佣金明细
        'comment'=>['rec_id','order_id','content'],  //评论
        'comment_list'=>['goods_id'],  //评论列表
        'collect'=>['collect_pid','collect_type','collect_is'],  //用户收藏
        'goods_collect'=>['goods_collect_is'],  //用户收藏列表
        'team'=>['team_level'],  //用户团队
        'loginphone'=>['wx_code','encryptedData','iv','phone_encryptedData','phone_iv'],//小程序授权+手机号
        'modify_payment_password'=>['password','code','code_type','scenes_id'],//修改支付密码
    ];
}