<?php

namespace app\apiadmin\logic\order;
use app\apiadmin\controller\Base;
use app\common\model\GoodsModel;
use app\common\model\GoodsSpecPriceModel;
use app\common\model\order\OrderCommentModel;
use app\common\model\user\UserModel;

class OrderCommentLogic extends Base
{
    //评论添加处理
    public function addHandle($data){
        $uid = (new UserModel())->where(['is_true'=>2])->orderRaw('rand()')->value('id');
        $data['aid'] = $this->admin_user['id']; //管理员id
        $data['is_virtual'] = 1;  //是否虚拟 1：是  2：不是
        $data['uid'] = $uid;   //用户id (随机用户)
        $data['ip'] = $_SERVER['REMOTE_ADDR'];
        $data['examine_is'] = 1;
        $data['examine_time'] = time();
        if (isset($data['gid']) && isset($data['spce_id'])){
            $price = (new GoodsSpecPriceModel)->where(['goods_id'=>$data['gid'],'item_id'=>$data['spce_id']])->field('key,key_name')->find();
            $data['spec_list_id'] =$price['key'];
            $data['spec_key_name'] =$price['key_name'];
        }
        $goods = (new GoodsModel())->findInfo(['goods_id'=>$data['gid']],'goods_name,identity_id');
        $data['goods_name'] = $goods['goods_name'];
        $data['identity_id'] =$goods['identity_id'];
        return $data;
    }

    //评论修改处理
    public function editHandle($data){
        $data['aid'] = $this->admin_user['id']; //管理员id
        return $data;
    }

    //商家评论回复
    public function replyHandle($data){
        $OrderCommentModel = new OrderCommentModel();
        $orderComment = $OrderCommentModel->where(['id'=>$data['id']])->field('id,uid,gid,order_sn,spce_id,spec_list_id')->find();
        $data['gid'] = $orderComment['gid'];
        $data['order_sn'] = $orderComment['order_sn'];
        $data['spce_id'] = $orderComment['spce_id'];
        $data['spec_list_id'] = $orderComment['spec_list_id'];
        $data['aid'] = $this->admin_user['id']; //管理员id
        $data['groups'] = $data['id'];
        $data['is_merchant'] = 1;//1 :商家回复
        $data['chase_comment_id'] = $data['id']; //追评id
        $data['examine_is'] = 1;
        $data['examine_time'] = time();
        $data['uid'] = $orderComment['uid'];
        $data['ip'] = $_SERVER['REMOTE_ADDR'];
        unset($data['id']);
        return $data;
    }
}