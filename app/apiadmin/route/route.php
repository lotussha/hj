<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
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

// 商品日志模块
Route::group('',function () {
    Route::rule('goods_log/lists','log.goods_log/lists'); // 分页获取商品日志列表 - sakuno
    Route::rule('goods_log/lists_by_goods','log.goods_log/lists_by_goods'); // 根据商品id获取商品日志列表 - sakuno
});

// 营销管理 - 秒杀模块
//Route::group('',function () {
//    // 秒杀时间段配置
//    Route::rule('seckill_config/add','seckill.SeckillConfig/add','POST'); // 新增秒杀配置 - sakuno
//    Route::rule('seckill_config/edit','seckill.SeckillConfig/edit'); // 查看 or 编辑 秒杀配置 - sakuno
//    Route::rule('seckill_config/delete','seckill.SeckillConfig/delete','POST'); // 删除秒杀配置 - sakuno
//    Route::rule('seckill_config/state','seckill.SeckillConfig/state','POST'); // 秒杀配置状态 - sakuno
//    Route::rule('seckill_config/lists','seckill.SeckillConfig/lists','GET'); // 秒杀配置列表(all) - sakuno
//    // 秒杀活动
//    Route::rule('seckill/add','seckill.Seckill/add','POST'); // 新增秒杀活动 - sakuno
//    Route::rule('seckill/read','seckill.Seckill/read','GET'); // 查看秒杀活动 - sakuno
//    Route::rule('seckill/edit','seckill.Seckill/edit','POST'); // 编辑秒杀活动 - sakuno
//    Route::rule('seckill/delete','seckill.Seckill/delete','POST'); // 删除秒杀活动 - sakuno
//    Route::rule('seckill/state','seckill.Seckill/state','POST'); // 秒杀活动状态 - sakuno
//});

// 营销管理 - 菜谱模块
Route::group('',function () {
    // 菜谱分类
    Route::rule('markering_menu_category/add','cookbook.MarkeringMenuCategory/add','POST'); // 新增菜谱分类 - lifenbao
    Route::rule('markering_menu_category/edit','cookbook.MarkeringMenuCategory/edit'); // 查看 or 编辑 菜谱分类 - lifenbao
    Route::rule('markering_menu_category/delete','cookbook.MarkeringMenuCategory/delete','POST'); // 删除菜谱分类 - lifenbao
    Route::rule('markering_menu_category/state','cookbook.MarkeringMenuCategory/state','POST'); // 菜谱分类状态 - lifenbao
    Route::rule('markering_menu_category/lists','cookbook.MarkeringMenuCategory/lists','GET'); // 菜谱分类列表(分页) - lifenbao
    Route::rule('markering_menu_category/lists_all','cookbook.MarkeringMenuCategory/lists_all','GET'); // 菜谱分类列表(all) - lifenbao
    // 菜谱
    Route::rule('markering_menu/add','cookbook.MarkeringMenu/add','POST'); // 新增菜谱 - lifenbao
    Route::rule('markering_menu/read','cookbook.MarkeringMenu/read','GET'); // 查看菜谱 - lifenbao
    Route::rule('markering_menu/edit','cookbook.MarkeringMenu/edit','POST'); // 编辑菜谱 - lifenbao
    Route::rule('markering_menu/delete','cookbook.MarkeringMenu/delete','POST'); // 删除菜谱 - lifenbao
    Route::rule('markering_menu/state','cookbook.MarkeringMenu/state','POST'); // 菜谱状态 - lifenbao
    Route::rule('markering_menu/lists','cookbook.MarkeringMenu/lists','GET'); // 菜谱列表(分页) - lifenbao
    Route::rule('markering_menu/goods_list','cookbook.MarkeringMenu/goods_list','GET'); //菜谱选择商品/获取商品商品信息 - lifenbao
});

//营销模块 - 优惠券
Route::group('', function(){
    //优惠券分类
    Route::rule('coupon_category/lists', 'coupon.CouponCategory/lists', 'GET');//优惠券分类列表
    Route::rule('coupon_category/add', 'coupon.CouponCategory/add', 'POST');//添加优惠券分类
    Route::rule('coupon_category/edit', 'coupon.CouponCategory/edit', 'POST');//修改优惠券分类
    Route::rule('coupon_category/del', 'coupon.CouponCategory/del', 'POST');//删除优惠券分类
    //优惠券
    Route::rule('coupon/lists', 'coupon.Coupon/lists', 'GET');//优惠券列表
    Route::rule('coupon/info', 'coupon.Coupon/info', 'GET');//优惠券详情
    Route::rule('coupon/add', 'coupon.Coupon/add', 'POST');//添加优惠券
    //Route::rule('coupon/edit', 'coupon.Coupon/edit', 'POST');//修改优惠券
    Route::rule('coupon/del', 'coupon.Coupon/del', 'POST');//删除优惠券
    Route::rule('issue/lists', 'coupon.CouponIssue/lists', 'POST');//优惠券领取列表
});

//营销模块 - 拼团
Route::group('', function(){
    Route::rule('makegroup/lists', 'activity.MakeGroup/lists', 'POST');//列表
    Route::rule('makegroup/info', 'activity.MakeGroup/info', 'POST');//详情
    Route::rule('makegroup/add', 'activity.MakeGroup/add', 'POST');//添加
    //Route::rule('makegroup/edit', 'activity.MakeGroup/edit', 'POST');//修改
    Route::rule('makegroup/del', 'activity.MakeGroup/del', 'POST');//删除
});

//营销模块 - 秒杀
Route::group('', function(){
    Route::rule('seckill/lists', 'activity.Seckill/lists', 'POST');//列表
    Route::rule('seckill/info', 'activity.Seckill/info', 'POST');//详情
    Route::rule('seckill/add', 'activity.Seckill/add', 'POST');//添加
    //Route::rule('seckill/edit', 'activity.Seckill/edit', 'POST');//修改
    Route::rule('seckill/del', 'activity.Seckill/del', 'POST');//删除
});

//营销模块 - 预售
Route::group('', function(){
    Route::rule('presale/lists', 'activity.PreSale/lists', 'POST');//列表
    Route::rule('presale/info', 'activity.PreSale/info', 'POST');//详情
    Route::rule('presale/add', 'activity.PreSale/add', 'POST');//添加
    //Route::rule('rush/edit', 'activity.Rush/edit', 'POST');//修改
    Route::rule('presale/del', 'activity.PreSale/del', 'POST');//删除
});

//营销模块 - 抢购
Route::group('', function(){
    Route::rule('rush/lists', 'activity.Rush/lists', 'POST');//列表
    Route::rule('rush/info', 'activity.Rush/info', 'POST');//详情
    Route::rule('rush/add', 'activity.Rush/add', 'POST');//添加
    //Route::rule('rush/edit', 'activity.Rush/edit', 'POST');//修改
    Route::rule('rush/del', 'activity.Rush/del', 'POST');//删除
});

//商品管理--jomlz
Route::group('',function (){
    Route::rule('goods/lists', 'goods.Goods/lists'); //商品列表
    Route::rule('goods/info', 'goods.Goods/info'); //商品信息
    Route::rule('goods/add_goods', 'goods.Goods/add_goods'); //添加商品
    Route::rule('goods/edit_goods', 'goods.Goods/edit_goods'); //编辑商品
    Route::rule('goods/edit_field', 'goods.Goods/edit_field'); //编辑商品字段
    Route::rule('goods/del_goods', 'goods.Goods/del_goods'); //删除商品
    Route::rule('goods/goods_spec_items', 'goods.Goods/goods_spec_items'); //获取商品模型
    //品牌
    Route::rule('goods/brand_lists', 'goods.Goods/brand_lists'); //品牌列表
    Route::rule('goods/get_brand_lists', 'goods.Goods/get_brand_lists'); //获取全部品牌
    Route::rule('goods/brand_info', 'goods.Goods/brand_info'); //品牌信息
    Route::rule('goods/brand_add', 'goods.Goods/brand_add'); //添加品牌
    Route::rule('goods/brand_edit', 'goods.Goods/brand_edit'); //编辑品牌
    Route::rule('goods/brand_del', 'goods.Goods/brand_del'); //删除品牌
    //分类
    Route::rule('goods_category/lists', 'goods.GoodsCategory/lists'); //分类列表
    Route::rule('goods_category/info', 'goods.GoodsCategory/info'); //分类信息
    Route::rule('goods_category/get_cat_tree_list', 'goods.GoodsCategory/get_cat_tree_list'); //分类级别分类树
    Route::rule('goods_category/add', 'goods.GoodsCategory/add'); //添加分类
    Route::rule('goods_category/edit', 'goods.GoodsCategory/edit'); //编辑分类
    Route::rule('goods_category/del', 'goods.GoodsCategory/del'); //删除分类
    //模型
    Route::rule('goods_type/type_lists', 'goods.GoodsType/type_lists'); //模型列表
    Route::rule('goods_type/get_goods_type_lists', 'goods.GoodsType/get_goods_type_lists'); //获取全部模型
    Route::rule('goods_type/type_info', 'goods.GoodsType/type_info'); //模型信息
    Route::rule('goods_type/type_add', 'goods.GoodsType/type_add'); //添加模型
    Route::rule('goods_type/type_edit', 'goods.GoodsType/type_edit'); //编辑模型
    Route::rule('goods_type/type_del', 'goods.GoodsType/type_del'); //删除模型
    Route::rule('goods_type/spec_list', 'goods.GoodsType/spec_list'); //规格列表
    Route::rule('goods_type/spec_item_info', 'goods.GoodsType/spec_item_info'); //规格信息
    Route::rule('goods_type/spec_item_add', 'goods.GoodsType/spec_item_add'); //添加规格
    Route::rule('goods_type/spec_item_edit', 'goods.GoodsType/spec_item_edit'); //编辑规格
    Route::rule('goods_type/spec_item_del', 'goods.GoodsType/spec_item_del'); //删除规格
    Route::rule('goods_type/attribute_list', 'goods.GoodsType/attribute_list'); //属性列表
    Route::rule('goods_type/attribute_info', 'goods.GoodsType/attribute_info'); //属性信息
    Route::rule('goods_type/attribute_add', 'goods.GoodsType/attribute_add'); //添加属性
    Route::rule('goods_type/attribute_edit', 'goods.GoodsType/attribute_edit'); //编辑属性
    Route::rule('goods_type/attribute_del', 'goods.GoodsType/attribute_del'); //删除属性
    //服务
    Route::rule('goods_service/lists', 'goods.GoodsService/lists'); //服务列表
    Route::rule('goods_service/info', 'goods.GoodsService/info'); //服务信息
    Route::rule('goods_service/add', 'goods.GoodsService/add'); //添加服务
    Route::rule('goods_service/edit', 'goods.GoodsService/edit'); //编辑服务
    Route::rule('goods_service/del', 'goods.GoodsService/del'); //删除服务
});
//运费--jomlz
Route::group('',function (){
    Route::rule('freight_template/lists', 'freight.FreightTemplate/lists'); //运费模板列表
    Route::rule('freight_template/freight_info', 'freight.FreightTemplate/freight_info'); //运费模板信息
    Route::rule('freight_template/freight_add', 'freight.FreightTemplate/freight_add'); //添加运费模板
    Route::rule('freight_template/freight_edit', 'freight.FreightTemplate/freight_edit'); //编辑运费模板
    Route::rule('freight_template/freight_del', 'freight.FreightTemplate/freight_del'); //删除运费模板
});


//订单管理
Route::group('', function () {
    //交易订单
    Route::rule('order/lists', 'order.order/lists');   //订单列表
    Route::rule('order/order_detail', 'order.order/order_detail'); //订单详情
    Route::rule('order/readjust_price', 'order.order/readjust_price'); //调整订单商品价格
    Route::rule('order/order_action', 'order.order/order_action'); //订单操作
    Route::rule('order/delivery_lists', 'order.order/delivery_lists'); //发货单列表
    Route::rule('order/delivery_detail', 'order.order/delivery_detail'); //发货单详情
    Route::rule('order/delever_handle', 'order.order/delever_handle'); //发货操作
    //售后维权
    Route::rule('orderaftersale/lists', 'order.OrderAfterSale/lists');  //售后列表
    Route::rule('orderaftersale/detail', 'order.OrderAfterSale/detail');  //售后详情
    Route::rule('orderaftersale/handle', 'order.OrderAfterSale/handle');  //售后详情
    Route::post('afterSaleCheck', 'order.OrderAfterSale/audit');
});

//Route::group('',function (){
//    Route::rule('user/list','user.User/index');  //用户列表
//});

//用户管理--hao
Route::group('',function (){
    //用户
    Route::group('User/',function (){
        Route::rule('add','add');  //添加虚拟用户
        Route::rule('list','index');  //用户列表
        Route::rule('info','info');//用户详情
        Route::rule('team','team');//用户团队
        Route::rule('integral','integral');//后台增加积分
        Route::rule('commission','commission');//后台增加佣金金额
        Route::rule('recharge','recharge');//后台增加充值金额
        Route::rule('integral_list','integral_list');//积分列表详情
        Route::rule('money_list','money_list');//金额列表详情
        Route::rule('modify','modify');//修改用户详情
        Route::rule('commission_list','commission_list');//佣金详情
    })->prefix('user.User/');

    //等级
    Route::group('UserGrade/',function (){
        Route::rule('list','index');  //等级列表
        Route::rule('info','info');  //等级详情
        Route::rule('add','add');  //添加等级
        Route::rule('edit','edit');  //修改等级
        Route::rule('status','status');  //等级禁用、启用
    })->prefix('user.UserGrade/');
});



//素材管理--hao
Route::group('',function (){
    //文章
    Route::group('',function (){
        Route::rule('ArticleIndex','index');  //文章列表
        Route::rule('ArticleInfo','article_info');  //文章详情
        Route::rule('ArticleAdd','article_add');  //文章添加
        Route::rule('ArticleEdit','article_edit');  //文章修改
        Route::rule('ArticleDel','article_del');  //文章删除
        Route::rule('ArticleTypeIndex','type_list');  //文章分类列表
        Route::rule('ArticleTypeInfo','type_info');  //文章分类详情
        Route::rule('ArticleTypeAdd','type_add');  //文章分类添加
        Route::rule('ArticleTypeEdit','type_edit');  //文章分类修改
        Route::rule('ArticleTypeDel','type_del');  //文章分类删除

    })->prefix('material.Article/');

    //专题
//    Route::group('',function (){
//        Route::rule('SpecialIndex','index','POST');  //文章列表
//        Route::rule('SpecialInfo','special_info','POST');  //文章详情
//        Route::rule('SpecialAdd','special_add','POST');  //文章添加
//        Route::rule('SpecialEdit','special_edit','POST');  //文章修改
//        Route::rule('SpecialDel','special_del','POST');  //文章删除
//        Route::rule('SpecialTypeIndex','type_list','POST');  //文章分类列表
//        Route::rule('SpecialTypeInfo','type_info','POST');  //文章分类详情
//        Route::rule('SpecialTypeAdd','type_add','POST');  //文章分类添加
//        Route::rule('SpecialTypeEdit','type_edit','POST');  //文章分类修改
//        Route::rule('SpecialTypeDel','type_del','POST');  //文章分类删除
//    })->prefix('material.Special/');

    //一键发圈
    Route::group('',function (){
        Route::rule('CoilingIndex','index');  //文章列表
        Route::rule('CoilingInfo','info');  //文章详情
        Route::rule('CoilingAdd','add');  //文章添加
        Route::rule('CoilingEdit','edit');  //文章修改
        Route::rule('CoilingDel','del');  //文章删除
    })->prefix('material.Coiling/');

    //消息
    Route::group('',function (){
        Route::rule('NewsIndex','index');  //消息列表
        Route::rule('NewsInfo','info');  //消息详情
        Route::rule('NewsAdd','add');  //消息添加
        Route::rule('NewsEdit','edit');  //消息修改
        Route::rule('NewsDel','del');  //消息删除
    })->prefix('material.News/');
});


    //轮播图
    Route::group('',function (){
        Route::rule('BannerIndex','banner_lists');  //轮播图列表
        Route::rule('BannerInfo','banner_info');  //轮播图详情
        Route::rule('BannerAdd','banner_add');  //轮播图添加
        Route::rule('BannerEdit','banner_edit');  //轮播图修改
        Route::rule('BannerDel','banner_del');  //轮播图删除
        Route::rule('BannerStatus','banner_status');  //轮播图禁用、启用
        Route::rule('BannerPositionIndex','position_lists');  //轮播图位置列表
        Route::rule('BannerPositionInfo','position_info');  //轮播图位置详情
        Route::rule('BannerPositionAdd','position_add');  //轮播图位置添加
        Route::rule('BannerPositionEdit','position_edit');  //轮播图位置修改
        Route::rule('BannerPositionDel','position_del');  //轮播图位置删除
    })->prefix('advertisement.Banner/');



//入驻管理--hao
Route::group('',function (){
    //入驻
    Route::group('',function (){
        Route::rule('SettlementIndex','index','POST');  //入驻列表
        Route::rule('SettlementInfo','info','POST');  //入驻详情
        Route::rule('SettlementAdd','add','POST');  //入驻添加
        Route::rule('SettlementEdit','edit','POST');  //入驻修改
        Route::rule('SettlementStatus','status','POST');  //身份禁用
        Route::rule('SettlementExamine','examine','POST');  //入驻审核
    })->prefix('settlement.Settlement/');

    //仓库
    Route::group('',function (){
        Route::rule('WarehouseIndex','index','POST');  //仓库列表
        Route::rule('WarehouseInfo','info','POST');  //仓库详情
        Route::rule('WarehouseAdd','add','POST');  //仓库添加
        Route::rule('WarehouseEdit','edit','POST');  //仓库修改
        Route::rule('WarehouseStatus','status','POST');  //仓库禁用/启用

    })->prefix('settlement.Warehouse/');
});

//评论--hao
Route::group('OrderComment',function (){
    Route::rule('index','index','POST');  //评论列表
    Route::rule('info','info','POST');  //评论详情
    Route::rule('add','add','POST');  //评论添加
    Route::rule('edit','edit','POST');  //修改评论
    Route::rule('edit','edit','POST');  //修改评论
    Route::rule('del','del','POST');  //评论删除
    Route::rule('reply','reply','POST');  //商家回复

})->prefix('order.OrderComment/');


//网站设置--hao
Route::group('WebsiteConfig',function (){
    Route::rule('list','index','POST');  //网站详情
    Route::rule('edit','edit','POST');  //网站详情
})->prefix('config.WebsiteConfig/');

//短信场景--hao
Route::group('ShortMessageSceneConfig',function (){
    Route::rule('list','index','POST');  //短信场景列表
    Route::rule('add','add','POST');  //短信场景添加
    Route::rule('edit','edit','POST');  //短信场景修改
    Route::rule('del','del','POST');  //短信场景删除
})->prefix('config.ShortMessageSceneConfig/');

//短信模板--hao
Route::group('ShortMessageModelConfig',function (){
    Route::rule('list','index','POST');  //短信场景列表
    Route::rule('info','info','POST');  //短信场景详情
    Route::rule('add','add','POST');  //短信场景添加
    Route::rule('edit','edit','POST');  //短信场景修改
    Route::rule('del','del','POST');  //短信场景删除

})->prefix('config.ShortMessageModelConfig/');

//短信接口--hao
Route::group('ShortMessageInterfaceConfig',function (){
    Route::rule('list','index','POST');  //短信接口列表
    Route::rule('info','info','POST');  //短信接口详情
    Route::rule('add','add','POST');  //短信接口添加
    Route::rule('edit','edit','POST');  //短信接口修改
    Route::rule('del','del','POST');  //短信接口删除

})->prefix('config.ShortMessageInterfaceConfig/');


//充值选项--hao
Route::group('RechargeOption',function (){
    Route::rule('list','index','POST');  //充值选项列表
    Route::rule('info','info','POST');  //充值选项详情
    Route::rule('add','add','POST');  //充值选项添加
    Route::rule('edit','edit','POST');  //充值选项修改
    Route::rule('status','status','POST');  //充值选项状态
    Route::rule('del','del','POST');  //充值选项删除
})->prefix('config.RechargeOption/');

//公告--hao
Route::group('Notice',function (){
    Route::rule('list','index','POST');  //公告列表
    Route::rule('info','info','POST');  //公告详情
    Route::rule('add','add','POST');  //公告添加
    Route::rule('edit','edit','POST');  //公告修改
    Route::rule('status','status','POST');  //公告状态
    Route::rule('del','del','POST');  //公告删除
})->prefix('config.Notice/');


//小程序菜单--hao
Route::group('AppletsMenu',function (){
    Route::rule('list','index','POST');  //小程序菜单列表
    Route::rule('info','info','POST');  //小程序菜单详情
    Route::rule('add','add','POST');  //小程序菜单添加
    Route::rule('edit','edit','POST');  //小程序菜单修改
    Route::rule('status','status','POST');  //小程序菜单状态
    Route::rule('del','del','POST');  //小程序菜单删除
})->prefix('config.AppletsMenu/');


//用户充值记录--hao
Route::group('UserRechargeLog',function (){
    Route::rule('list','index','POST');  //用户充值记录列表

})->prefix('user.UserRechargeLog/');

//提现记录--hao
Route::group('UserCash',function (){
    Route::rule('list','index');  //提现记录列表
    Route::rule('examine','examine');  //审核提现记录列表
})->prefix('user.UserCash/');


//管理员--hao
Route::group('admin',function (){
    Route::rule('lists','lists');  //管理员列表
    Route::rule('edit','edit');  //管理员修改
    Route::rule('add','add');  //管理员添加
    Route::rule('info','info');  //管理员详情
    Route::rule('status','status');  //管理员管理员启用、禁用

})->prefix('admin.AdminUser/');


//店铺轮播图--hao
Route::group('shopBanner',function (){
    Route::rule('lists','lists');//店铺轮播图列表
    Route::rule('edit','edit');//店铺轮播图修改
    Route::rule('add','add');//店铺轮播图添加
    Route::rule('info','info');//店铺轮播图详情
    Route::rule('status','status');//店铺轮播图状态
    Route::rule('del','del');//店铺轮播图删除
})->prefix('admin.shopBanner/');