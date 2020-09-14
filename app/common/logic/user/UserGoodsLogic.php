<?php


namespace app\common\logic\user;

//操作商品
use app\api\logic\order\OrderLogic;
use app\apiadmin\controller\cookbook\MarkeringMenu;
use app\common\logic\settlement\SettlementLogic;
use app\common\model\cookbook\MarkeringMenuModel;
use app\common\model\GoodsModel;
use app\common\model\GoodsSpecPriceModel;
use app\common\model\order\OrderCommentModel;
use app\common\model\order\OrderGoodsModel;
use app\common\model\order\OrderModel;
use app\common\model\settlement\SettlementModel;
use app\common\model\user\UserCollectModel;
use app\common\model\user\UserModel;
use sakuno\utils\JsonUtils;
use think\facade\Db;

class UserGoodsLogic
{


    /**
     * 用户评论
     * User: hao  2020-8-26
     */
    public function comment($receive)
    {
        $res = (new OrderCommentModel())->findInfo(['rec_id' => $receive['rec_id'], 'order_id' => $receive['order_id'], 'groups' => 0]);
        if ($res) {
            return JsonUtils::fail('订单已评论');
        }
        if (isset($receive['img_url'])) {
            if (!isImg($receive['img_url'])) {
                return JsonUtils::fail('图片格式有误');
            }
            $img = explode(',', $receive['img_url']);
            if (count($img) > 6) {
                return JsonUtils::fail('图片不能超过6张');
            }
        }
        $price = new GoodsSpecPriceModel;

        $data_comment = array();

        $data_comment['order_id'] = $receive['order_id'];
        $data_comment['uid'] = $receive['uid'];
        $data_comment['rec_id'] = $receive['rec_id'];


        $where_order = array();
        $where_order[] = ['order_id', '=', $receive['order_id']];
        $where_order[] = ['rec_id', '=', $receive['rec_id']];
        $order_goods = (new OrderGoodsModel())->findInfo($where_order, 'goods_id,goods_name,spec_key,spec_key_name,identity_id');
        if (isset($receive['goods_id']) && $order_goods['spec_key']) {
            $data_comment['spce_id'] = $price->where(['goods_id' => $receive['goods_id'], 'key' => $order_goods['spec_key']])->value('id');
        }
        $data_comment['gid'] = $order_goods['goods_id'];
        $data_comment['spec_list_id'] = $order_goods['spec_key'];
        $data_comment['goods_name'] = $order_goods['goods_name'];
        $data_comment['identity_id'] = $order_goods['identity_id'];
        $data_comment['content'] = $receive['content'];
        $data_comment['img_url'] = $receive['img_url'];
        $data_comment['ip'] = $_SERVER['REMOTE_ADDR'];
        Db::startTrans();
        try {
            $res = (new OrderCommentModel())->addInfo($data_comment);
            if (!$res) {
                Db::rollback();
                return JsonUtils::fail('评论失败2');
            }

            $res = (new OrderLogic())->changeOrderComment($receive['uid'], $receive['order_id'], $receive['rec_id']);
            if ($res['status'] == 0) {
                Db::rollback();
                return JsonUtils::fail($res['msg']);
            }
            Db::commit();
            return JsonUtils::successful('评论成功');
        } catch (\Exception $e) {
            Db::rollback();
            return JsonUtils::fail('评论失败1');
        }
    }


    /**
     * 用户收藏
     * User: hao  2020-8-31
     */
    public function collect($receive)
    {
        if ($receive['collect_is'] == 1) {
            $where = array();
            $where[] = ['user_id', '=', $receive['uid']];
            $where[] = ['pid', '=', $receive['collect_pid']];
            $where[] = ['type', '=', $receive['collect_type']];

            $id = (new UserCollectModel())->getValues($where, 'id');
            if ($id) {
                return JsonUtils::fail('已收藏，请勿重复收藏');
            }
            Db::startTrans();
            try {
                //添加收藏
                $data = array();
                $data['pid'] = $receive['collect_pid'];
                $data['type'] = $receive['collect_type'];
                $data['user_id'] = $receive['uid'];
                $res = (new UserCollectModel())->addInfo($data);
                if (!$res) {
                    Db::rollback();
                    return JsonUtils::fail('收藏失败');
                }
                switch ($receive['collect_type']){
                    //商品
                    case 1:
                        break;
                    //店铺
                    case 2:
                        $res = (new SettlementModel())->setDataInc(['admin_id'=>$receive['collect_pid']],'collect_num',1);
                        if (!$res) {
                            Db::rollback();
                            return JsonUtils::fail('收藏失败');
                        }
                        break;
                    //菜谱
                    case 3:
                        $res = (new MarkeringMenuModel())->setDataInc(['admin_id'=>$receive['collect_pid']],'collection',1);
                        if (!$res) {
                            Db::rollback();
                            return JsonUtils::fail('收藏失败');
                        }
                        break;
                }
                Db::commit();

                return JsonUtils::successful('收藏成功');
            }catch (\Exception $e){
                return JsonUtils::fail('服务器异常');

                Db::rollback();
            }

        } else {
            Db::startTrans();

            try {
                $where = array();
                $where[] = ['user_id', '=', $receive['uid']];
                $where[] = ['pid', '=', $receive['collect_pid']];
                $where[] = ['type', '=', $receive['collect_type']];
                $res = (new UserCollectModel())->deleteInfo($where, 1);
                if ($res === false) {
                    return JsonUtils::fail('收藏夹id不能为空');
                }
                switch ($receive['collect_type']){
                    //商品
                    case 1:
                        break;
                    //店铺
                    case 2:
                        $res = (new SettlementModel())->setDataDec(['admin_id'=>$receive['collect_pid']],'collect_num',1);
                        if (!$res) {
                            Db::rollback();
                            return JsonUtils::fail('收藏失败');
                        }
                        break;
                    //菜谱
                    case 3:
                        $res = (new MarkeringMenuModel())->setDataDec(['admin_id'=>$receive['collect_pid']],'collection',1);
                        if (!$res) {
                            Db::rollback();
                            return JsonUtils::fail('收藏失败');
                        }
                        break;
                }
                Db::commit();

                return JsonUtils::successful('取消收藏成功');
            }catch (\Exception $e){
                Db::rollback();

            }

        }
    }


    /**
     * 用户商品收藏列表（好物圈）
     * User: hao  2020-8-31
     */
    public function goods_collect($receive)
    {
        if ($receive['goods_collect_is'] == 1) {
            //用户商品收藏
            $where = array();
            $where[] = ['user_id', '=', $receive['uid']];
            $where[] = ['type', '=', 1];
            $goods_id = (new UserCollectModel())->where($where)->order('create_time desc')->column('pid');
            $goods_id = implode(',', $goods_id);
        } else {
            //用户已购买
            //获取购买过的总订单号
            $where_order = array();
            $where_order[] = ['user_id', '=', $receive['uid']];
            $where_order[] = ['parent_id', '=', '0'];
            $where_order[] = ['pay_status', '=', '1'];
            $order_id_list = (new OrderModel())->where($where_order)->order('add_time desc')->column('id');
            $order_id_list = implode(',', $order_id_list);

            $where_order_goods = array();
            $where_order_goods[] = ['order_id', 'in', $order_id_list];
            $order_goods_id_list = (new OrderGoodsModel())->where($where_order_goods)->order('rec_id desc')->column('goods_id');

            $order_goods_id_list = array_unique($order_goods_id_list);
            $goods_id = implode(',', $order_goods_id_list);
        }
        $where_goods = array();
        $goods_id = $goods_id ?$goods_id: '0';
        $where_goods[] = ['goods_id', 'in', $goods_id];
        $field = 'goods_id,goods_name,market_price,shop_price,original_img,one_distribution_price';
        $receive['list_rows'] = $receive['list_rows'] ?? 10;
        $exp = Db::raw('field(goods_id,' . $goods_id . ')');

        $list = (new GoodsModel())
            ->field($field)
            ->where([['is_on_sale', '=', 1], ['is_check', '=', 1]])
            ->where($where_goods)
            ->order($exp)
            ->scope('where', $receive)
            ->paginate($receive['list_rows']);
        $list = $list->toArray();
        return JsonUtils::successful('操作成功', $list);
    }


    /**
     * 用户收藏店铺
     * User: hao  2020-8-31
     */
    public function shop_collect($receive){
        //用户商品收藏
        $where = array();
        $where[] = ['user_id', '=', $receive['uid']];
        $where[] = ['type', '=', 2];
        $shop_id = (new UserCollectModel())->where($where)->order('create_time desc')->column('pid');
        $shop_id = implode(',', $shop_id);

        $where_shop = array();
        $shop_id = $shop_id ?$shop_id: '0';
        $where_shop[] = ['admin_id', 'in', $shop_id];
        $field = 'admin_id,tel,nickname,logo_img';
        $receive['list_rows'] = $receive['list_rows'] ?? 10;
        $exp = Db::raw('field(admin_id,' . $shop_id . ')');

        $list = (new SettlementModel())
            ->field($field)
            ->where($where_shop)
            ->order($exp)
            ->scope('where', $receive)
            ->paginate($receive['list_rows']);
        $list = $list->toArray();
        return JsonUtils::successful('操作成功', $list);

    }



    /**
     * 用户收藏菜谱列表
     * User: hao  2020-8-31
     */
    public function markering_collect($receive){
        //用户商品收藏
        $where = array();
        $where[] = ['user_id', '=', $receive['uid']];
        $where[] = ['type', '=', 3];
        $markering_id = (new UserCollectModel())->where($where)->order('create_time desc')->column('pid');
        $markering_id = implode(',', $markering_id);

        $where_shop = array();
        $markering_id = $markering_id ?$markering_id: '0';
        $where_markering[] = ['id', 'in', $markering_id];
        $field = 'id,menu_title,main_images,difficulty,cooking_time,user_id';
        $receive['list_rows'] = $receive['list_rows'] ?? 10;
        $exp = Db::raw('field(id,' . $markering_id . ')');

        $list = (new MarkeringMenuModel())
            ->field($field)
            ->where($where_shop)
            ->order($exp)
            ->scope('where', $receive)
            ->paginate($receive['list_rows']);

        foreach ($list as $key=>$value){
            $user = (new UserModel())->findInfo(['id'=>$value['user_id']],'nick_name,avatar_url');
            $value['nick_name'] = $user['nick_name'];
            $value['avatar_url'] = $user['avatar_url'];
            $list[$key] = $value;
        }
        $list = $list->toArray();
        return JsonUtils::successful('操作成功', $list);


    }



}