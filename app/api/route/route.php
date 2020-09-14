<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: jomlz
 *           '::::::::::::::..                                   Time: 2020/8/10 15:19
 *                ..::::::::::::.                                女神保佑，代码无bug！！！
 *              ``::::::::::::::::                               Codes are far away from bugs with the goddess！！！
 *               ::::``:::::::::'        .:::.
 *              ::::'   ':::::'       .::::::::.
 *            .::::'      ::::     .:::::::'::::.
 *           .:::'       :::::  .:::::::::' ':::::.
 *          .::'        :::::.:::::::::'      ':::::.
 *         .::'         ::::::::::::::'         ``::::.
 *     ...:::           ::::::::::::'              ``::.
 *    ````':.          ':::::::::'                  ::::..
 *                       '.:::::'                    ':'````..
 *
 */
use think\facade\Route;

//首页
Route::rule('index', 'index');

//商品--jomlz
Route::group('',function (){
    Route::rule('goods/get_lists', 'goods.Goods/get_lists'); //获取商品列表
    Route::rule('goods/goods_details', 'goods.Goods/goods_details'); //获取商品列表
    Route::rule('goods/index_seckill_lists', 'goods.Goods/index_seckill_lists'); //获取首页秒杀商品列表
    Route::rule('goods/flash_sale_time_space', 'goods.Goods/flash_sale_time_space'); //获取秒杀时间组
    Route::rule('goods/seckill_lists', 'goods.Goods/seckill_lists'); //获取秒杀商品列表
    Route::rule('goods/get_spec_price', 'goods.Goods/get_spec_price'); //获取商品规格价格
});

//商品分类
Route::group('',function (){
    Route::rule('goods_category/lists', 'goods.GoodsCategory/lists'); //获取分类树
    Route::rule('goods_category/recommend', 'goods.GoodsCategory/recommend'); //获取推荐分类
});

//购物车-jomlz
Route::group('',function (){
    Route::rule('cart/index', 'cart.Cart/index'); //获取商品列表
    Route::rule('cart/add_cart', 'cart.Cart/add_cart'); //添加购物车
    Route::rule('cart/change_num', 'cart.Cart/change_num'); //购物车修改数量
    Route::rule('cart/change_selected', 'cart.Cart/change_selected'); //购物车修改数量
    Route::rule('cart/delete', 'cart.Cart/delete'); //删除购物车商品
});

//订单管理-jomlz
Route::group('',function (){
    Route::rule('order/goods_confirm_order', 'order.Order/goods_confirm_order'); //商品确认订单
    Route::rule('order/cart_confirm_order', 'order.Order/cart_confirm_order'); //购物车确认订单
    Route::rule('order/goods_add_order', 'order.Order/goods_add_order'); //商品提交订单
    Route::rule('order/cart_add_order', 'order.Order/cart_add_order'); //购物车提交订单
    Route::rule('order/my_order', 'order.Order/my_order'); //我的订单
    Route::rule('order/order_goods_info', 'order.Order/order_goods_info'); //订单商品信息
    Route::rule('order/order_details', 'order.Order/order_details'); //订单详情
    Route::rule('order/cancel_order', 'order.Order/cancel_order'); //取消订单
    Route::rule('order/del_order', 'order.Order/del_order'); //删除订单
    Route::rule('order/logistics_info', 'order.Order/logistics_info'); //查看物流
    Route::rule('order/confirm_receipt', 'order.Order/confirm_receipt'); //确认收货
    Route::rule('order/del_order', 'order.Order/del_order'); //订单评价
    //预售
    Route::rule('order_pre_sale/lists', 'order.OrderPreSale/lists'); //预售订单列表
    Route::rule('order_pre_sale/details', 'order.OrderPreSale/details'); //预售订单详情
});

//退款售后
Route::group('',function (){
    Route::rule('after_sale/lists', 'order.OrderAfterSale/lists'); //售后列表
    Route::rule('after_sale/apply', 'order.OrderAfterSale/apply'); //申请售后
    Route::rule('after_sale/details', 'order.OrderAfterSale/details'); //申请售后
    Route::rule('after_sale/user_delivery', 'order.OrderAfterSale/user_delivery'); //售后用户发货
    Route::rule('after_sale/cancel', 'order.OrderAfterSale/cancel'); //售后取消
});

//支付管理-jomlz
Route::group('',function (){
    Route::rule('pay/pay_parameter', 'pay.Pay/pay_parameter'); //获取支付参数
    Route::rule('pay/check_pay_status', 'pay.Pay/check_pay_status'); //检查支付状态
    Route::rule('pay/pay_notify', 'pay.PayNotify/pay_notify'); //支付回调
    Route::rule('pay/passpay', 'pay.PayNotify/passpay'); //测试--hao
    Route::rule('order_balance', 'user.UserDetails/order_balance'); //测试--hao
    Route::rule('refund_balance', 'user.UserDetails/refund_balance'); //测试--hao
    Route::rule('order_completion', 'user.UserDetails/order_completion'); //测试--hao
});

//身份管理
Route::group('',function (){
    Route::rule('identity/lists', 'identity.Identity/lists'); //获取身份列表
    Route::rule('identity/goods_list', 'identity.Identity/goods_list'); //身份商品列表
});

//登录--hao
Route::rule('LoginHandle/loginHome','LoginHandle/loginHome');
Route::rule('LoginHandle/loginPhone','LoginHandle/loginPhone');

//优惠券
Route::group('', function(){
    Route::rule('coupon/get_coupon', 'goods.Coupon/get_coupon');//领取优惠券
    Route::rule('coupon/get_coupon_info', 'goods.Coupon/get_coupon_info');//优惠券详情
    Route::rule('coupon/get_coupon_list', 'goods.Coupon/get_coupon_list');//优惠券列表
    Route::rule('coupon/state', 'goods.Coupon/state');//使用优惠券
});


//营销活动 - 秒杀
Route::group('', function(){
    Route::rule('seckill/get_list', 'activity.Seckill/get_list');//秒杀列表
});


//用户地址--hao
Route::group('UserAddress',function (){
    Route::rule('list','index','POST');  //用户地址列表
    Route::rule('info','info','POST');  //用户地址详情
    Route::rule('add','add','POST');  //用户地址添加
    Route::rule('edit','edit','POST');  //用户地址修改
    Route::rule('del','del','POST');  //用户地址删除
})->prefix('user.UserAddress/');


//用户银行卡--hao
Route::group('UserBank',function (){
    Route::rule('list','index','POST');  //用户银行卡列表
    Route::rule('info','info','POST');  //用户银行卡详情
    Route::rule('add','add','POST');  //用户银行卡添加
    Route::rule('edit','edit','POST');  //用户银行卡修改
    Route::rule('del','del','POST');  //用户银行卡删除
})->prefix('user.UserBank/');


//用户详情--hao
Route::group('UserDetails',function (){
    Route::rule('edit_information','edit_information','POST');  //修改用户资料
    Route::rule('bind_phone','bind_phone','POST');  //绑定手机
    Route::rule('share','share','POST');  //用户分享二维码
    Route::rule('poster','poster','POST');  //用户分享海报与设置
    Route::rule('recharge','recharge','POST');  //用户充值
    Route::rule('get_cash','get_cash','POST');  //用户可提现数据
    Route::rule('cash','cash','POST');  //用户提现
    Route::rule('personal','personal','POST');  //个人中心
    Route::rule('sign_page','sign_page','POST');  //签到页面
    Route::rule('sign_user','sign_user','POST');  //用户签到
    Route::rule('team','team','POST');  //用户团队
    Route::rule('modify_payment_password','modify_payment_password','POST');  //用户修改支付密码

})->prefix('user.UserDetails/');

//我的钱包--hao
Route::group('UserWallet',function (){
    Route::rule('wallet','wallet','POST');  //我的钱包
    Route::rule('recharge_detail','recharge_detail','POST');  //充值明细
    Route::rule('integral_detail','integral_detail','POST');  //积分明细
    Route::rule('cash_detail','cash_detail','POST');  //提现明细
    Route::rule('commission_detail','commission_detail','POST');  //佣金明细
    Route::rule('balance_detail','balance_detail','POST');  //余额明细
    Route::rule('get_bank','get_bank','POST');  //银行列表

})->prefix('user.UserWallet/');


//用户操作商品--hao
Route::group('UserGoods',function (){
    Route::rule('comment','comment','POST');  //评论
    Route::rule('comment_list','comment_list','POST');  //评论列表
    Route::rule('collect','collect','POST');  //用户收藏
    Route::rule('goods_collect','goods_collect','POST');  //用户商品列表
    Route::rule('shop_collect','shop_collect','POST');  //收藏店铺列表
    Route::rule('markering_collect','markering_collect','POST');  //收藏菜谱列表
    Route::rule('me_comment','me_comment','POST');  //我评论的列表
})->prefix('user.UserGoods/');

//用户入驻--hao
Route::group('Settlement',function (){
    Route::rule('add_settlement','add_settlement','POST');  //申请入驻
    Route::rule('settlement_details','settlement_details','POST');  //用户申请入驻详情
    Route::rule('edit_settlement','edit_settlement','POST');  //用户修改申请入驻详情

})->prefix('user.Settlement/');

//文案--hao
Route::group('UserMaterial',function (){
    Route::rule('coiling','coiling','POST');  //一键发圈
    Route::rule('article_type','article_type','POST');  //文章分类
    Route::rule('article','article','POST');  //文章
    Route::rule('article_details','article_details','POST');  //文章详情
    Route::rule('news','news','POST');  //消息详情
})->prefix('user.UserMaterial/');

//营销活动 菜谱模块--lifenbao
Route::group('CookBook',function (){
    Route::rule('get_lists','get_lists','POST');       //菜谱列表
    Route::rule('get_details','get_details','POST');   //菜谱详情页
    Route::rule('get_category','get_category','POST'); //菜谱分类
    Route::rule('ck_comment','ck_comment','POST');     //菜谱添加评论
    Route::rule('ck_comment_list','ck_comment_list','POST'); //菜谱评论列表
    Route::rule('reply_comment','reply_comment','POST');     //菜谱回复评论
    Route::rule('comment_details','comment_details','POST'); //评论详情列表
})->prefix('cookbook.CookBook/');




//设置
Route::group('Config',function (){
    Route::rule('banner','banner','POST');  //轮播图
})->prefix('config.Config/');



//公共方法
Route::group('apiCommon',function (){
    Route::rule('get_level_region_lists','get_level_region_lists','POST');  //获取级别地区列表
    Route::rule('upload_image_file','upload_image_file','POST');  //file文件上传
    Route::rule('upload_image_base64','upload_image_base64','POST');  //base64文件上传
    Route::rule('get_code','get_code','POST');  //获取短信
})->prefix('ApiCommon/');

//门店登录
Route::rule('loginAdmin/login','LoginAdmin/login');//店铺登录
Route::rule('loginAdmin/modify_password','LoginAdmin/modify_password');  //店铺修改密码


//门店
Route::group('adminDetails',function (){
    Route::rule('shop_center','shop_center','POST');  //获取级别地区列表
})->prefix('admin.AdminDetails/');

