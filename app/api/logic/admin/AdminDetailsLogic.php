<?php


namespace app\api\logic\admin;

//门店处理方法
use app\apiadmin\model\AdminUsers;
use app\common\model\order\OrderCommentModel;
use app\common\model\user\UserCollectModel;
use sakuno\utils\JsonUtils;

class AdminDetailsLogic
{
    //店铺中心
    public function shop_center($receive){
        $data = array();
        $adminUser = (new AdminUsers())->where(['id'=>$receive['aid']])->field('nickname,avatar')->find();
        //店铺头像
        $data['nickname'] = $adminUser['nickname'];
        //店铺名称
        $data['shop_logo'] = $adminUser['avatar'];
        //今日销量金额
        $data['day_sales_money'] =0;
        //今天收益
        $data['day_income_money'] =0;
        //总销量金额
        $data['total_sales_money'] = 0;
        //总收益
        $data['total_income_money'] =0;

        //门店粉丝
        $data['fans'] = (new UserCollectModel())->where(['pid'=>$receive['aid'],'type'=>2])->count();

        //门店评论
        $data['comment'] = (new OrderCommentModel())->where(['identity_id'=>$receive['aid'],'is_delete'=>0,'groups'=>0])->count();

        //订单
        $order = array();
        $order['wait_payment'] = 0;//待付款
        $order['wait_ship'] = 0;//待发货
        $order['wait_receipt'] = 0;//待收货
        $order['refund'] = 0;//退款、售后
        $order['pre_sale_order'] = 0;//预售订单
        $order['group_order'] = 0;//平团订单
        $order['wait_order'] = 0;//待提订单
        $data['order'] = $order;

        return JsonUtils::successful('操作成功',['list'=>$data]);
    }
}