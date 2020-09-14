<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/22
 * Time: 14:50
 */

namespace app\api\logic\order;

use app\api\logic\goods\GoodsLogic;
use app\common\logic\FreightTemplateLogic;
use app\common\logic\goods\GoodsPromFactory;
use app\common\model\cart\GoodsCartModel;
use app\common\model\GoodsModel;
use app\common\model\GoodsSpecPriceModel;
use app\common\model\order\OrderGoodsModel;
use app\common\model\order\OrderModel;
use app\common\model\PayLogModel;
use app\common\model\user\UserAddressModel;
use app\common\model\user\UserGradeModel;
use app\common\model\user\UserModel;
use sakuno\utils\JsonUtils;
use think\facade\Db;

class OrderLogic
{
    /**
     * 确认订单
     * User: Jomlz
     */
    public function confirmOrder($param = [])
    {
        $addressModel = new UserAddressModel();
        $cartModel = new GoodsCartModel();
        //用户收货地址
        $address_id = $param['address_id'] ?? '';
        $address_where = ['user_id' => $param['user_id'], 'is_delete' => 0];
        $user_address = $addressModel->field('address_id,user_id,consignee,province,city,county,twon,address,mobile,is_default')
            ->where($address_where)
            ->scope('where', $param)
            ->order('is_default desc')
            ->find();
        if ($address_id && empty($user_address)) {
            return ['status' => 0, 'msg' => '收货地址参数有误'];
        }
        $user_address = $user_address ? $user_address->toArray() : [];
        $region_id = $user_address['twon'] ?? 0;
        $goods_lists = [];
        //商品下单
        if (isset($param['goods_id'])) {
            $res = $this->goodsSpec($param['goods_id'], $param['key'], $param['goods_num'],$param['user_id']);
            if ($res['status'] == 1) {
                $good_info = $res['good_info'];
                array_push($goods_lists, $good_info);
            } else {
                return ['status' => 0, 'msg' => $res['msg']];
            }
        }
        //购物车下单
        if (isset($param['cart_id'])) {
            $cart_id = $param['cart_id'];
            $cart_lists = $cartModel
                ->where(['user_id' => $param['user_id'], 'is_del' => 0])
                ->where('id', 'in', $cart_id)
                ->select()->toArray();
            if (empty($cart_lists)) {
                return ['status' => 0, 'msg' => '购物车参数错误'];
            }
            foreach ($cart_lists as $k => $v) {
                if (!$cartModel->checkAddCart($v['prom_type'])){
                    return ['status' => 0, 'msg' => '活动参数错误'];
                }
                $res = $this->goodsSpec($v['goods_id'], $v['spec_key'], $v['goods_num'],$param['user_id']);
                if ($res['status'] == 1) {
                    $good_info = $res['good_info'];
                    array_push($goods_lists, $good_info);
                } else {
                    return ['status' => 0, 'msg' => $res['msg']];
                }
            }
        }
        $identity_id_array = $this->orderGroup($goods_lists, $region_id);
        $total = 0;
        $total_discount_price = 0;
        $total_freight_price = 0;
        $total_goods_price = 0;
        foreach ($identity_id_array as $k => $v) {
//            $identity_id_array[$k]['subtotal_price'] = $v['goods_price'] + $v['freight_price']; //商品小计
            $identity_id_array[$k]['subtotal_price'] = $v['discount_price'] + $v['freight_price']; //折扣小计
            $identity_info = Db::name('settlement')->field('nickname')->where(['admin_id' => $v['identity_id']])->find();
            $identity_id_array[$k]['identity_info']['identity_name'] = $identity_info['nickname'] ?? '';
            $identity_goods_list = [];
            $prom_types = '';
            $prom_ids = '';
            $paid_money = 0;
            $goods_num = 0;
            foreach ($goods_lists as $kk => $vv) {
                if ($vv['identity'] == $v['identity'] && $vv['identity_id'] == $v['identity_id']) {
                    if ($vv['prom_type'] > 0){
                        $prom_types .= $vv['prom_type'] .',';
                        $prom_ids .= $vv['prom_id'] .',';
                    }
                    $paid_money += $vv['deposit'] * $vv['goods_num'];
                    $goods_num += $vv['goods_num'];
                    array_push($identity_goods_list, $vv);
                }
            }
            $identity_id_array[$k]['prom_types'] = trim($prom_types,',');
            $identity_id_array[$k]['prom_ids'] = trim($prom_ids,',');
            $identity_id_array[$k]['paid_money'] = $paid_money;
            $identity_id_array[$k]['goods_num'] = $goods_num;
            $identity_id_array[$k]['goods_list'] = $identity_goods_list;
            $total += $v['goods_price'] + $v['freight_price'];
            $total_discount_price += $v['discount_price'] + $v['freight_price'];
//            $total_goods_price += $v['goods_price'];
            $total_goods_price += $v['discount_price'];
            $total_freight_price += $v['freight_price'];
        }
        $total_info = [
            'total_freight_price' => $total_freight_price,  //总运费价格
            'total_goods_price ' => $total_goods_price,     //商品总价
            'total_discount' => 0,                          //优惠总价
            'total_price' => $total_discount_price,         //订单总价
//            'total_price' => $total,                        //订单总价
//            'total_discount_price' => $total_discount_price,//折扣后总价
            'prom_types' => trim($prom_types,',') ?? 0,
            'prom_ids' => trim($prom_ids,',') ?? 0,
        ];
        $data = ['user_address' => $user_address, 'list' => $identity_id_array, 'total_info' => $total_info];
        return ['status' => 1, 'msg' => '成功', 'data' => $data];

    }

    /**
     * 添加订单
     * User: Jomlz
     */
    public function addOrder($param = [], $orderInfo = '')
    {
        $user_id = $param['user_id'];
        $main_order_sn = mt_rand(1000000000000, 9999999999999);
        $user_address = $orderInfo['user_address'];
        $order_data = [
            'user_id' => $user_id,
            'order_sn' => $main_order_sn,
            'goods_price' => $orderInfo['total_info']['total_goods_price '],
            'actual_amount' => $orderInfo['total_info']['total_goods_price '],
            'order_amount' => $orderInfo['total_info']['total_price'],
            'shipping_price' => $orderInfo['total_info']['total_freight_price'],
            'add_time' => time(),
            'consignee' => $user_address['consignee'],
            'province' => $user_address['province'],
            'city' => $user_address['city'],
            'county' => $user_address['county'],
            'twon' => $user_address['twon'],
            'address' => $user_address['address'],
            'receiver_tel' => $user_address['mobile'],
            'user_note' => $param['user_note'] ?? '',
        ];
//        dump($orderInfo);die;
        $pay_body = '购买商品';
        // 启动事务
        Db::startTrans();
        try {
            $main_order_id = Db::name('order')->insertGetId($order_data);
            $log_id = (new PayLogModel())->insertPayLog($main_order_id, $main_order_sn, $orderInfo['total_info']['total_price'], 1, $user_id,'','',$pay_body);
            //根据身份分组保存身份子订单
            $order_lists = $orderInfo['list'];
            foreach ($order_lists as $k => $v) {
                $son_order_sn = mt_rand(1000000000000, 9999999999999);
                $son_order_data = [
                    'parent_id' => $main_order_id,
                    'user_id' => $user_id,
                    'order_sn' => $son_order_sn,
                    'identity' => $v['identity'],
                    'identity_id' => $v['identity_id'],
//                    'goods_price' => $v['goods_price'],
                    'goods_price' => $v['discount_price'], //折扣后的价格
                    'order_amount' => $v['subtotal_price'],
                    'actual_amount' => $v['subtotal_price'],
                    'shipping_price' => $v['freight_price'],
                    'add_time' => time(),
                    'consignee' => $user_address['consignee'],
                    'province' => $user_address['province'],
                    'city' => $user_address['city'],
                    'county' => $user_address['county'],
                    'twon' => $user_address['twon'],
                    'address' => $user_address['address'],
                    'receiver_tel' => $user_address['mobile'],
                    'user_note' => $param['user_note'] ?? '',
                    'prom_types' => $v['prom_types'] ?? '',
                    'prom_ids' => $v['prom_ids'] ?? '',
                    'paid_money' => $v['paid_money'] ?? 0,
                    'goods_num' => $v['goods_num'],
                ];
                $son_order_id = Db::name('order')->insertGetId($son_order_data);
                if($v['prom_types'] == 3){
                    //定金
                    $p_log_id = (new PayLogModel())->insertPayLog($son_order_id, $son_order_sn, $v['paid_money'], 1, $user_id, $log_id,'',$pay_body);
                    //尾款
                    $last_price = $orderInfo['total_info']['total_price'] - $v['paid_money'];
                    (new PayLogModel())->insertPayLog($son_order_id, $son_order_sn, $last_price, 1, $user_id, $log_id,'',$pay_body);
                }else{
                    $p_log_id = (new PayLogModel())->insertPayLog($son_order_id, $son_order_sn, $v['subtotal_price'], 1, $user_id, $log_id,'',$pay_body);
                }
                //根据商品保存订单商品
                $goodsList = $v['goods_list'];
                foreach ($goodsList as $kk => $vv) {
                    $order_goods = [
                        'order_id' => $son_order_id,
                        'order_parent_id' => $main_order_id,
                        'order_sn' => $son_order_sn,
                        'goods_id' => $vv['goods_id'],
                        'goods_name' => $vv['goods_name'],
                        'goods_sn' => $vv['goods_sn'],
                        'goods_num' => $vv['goods_num'],
                        'goods_price' => $vv['price'],
                        'discount_price' => $vv['discount_price'],
                        'cost_price' => $vv['cost_price'],
                        'identity' => $vv['identity'],
                        'identity_id' => $vv['identity_id'],
                        'item_id' => $vv['item_id'],
                        'spec_key' => $vv['spec_key'],
                        'spec_key_name' => $vv['spec_key_name'],
                        'one_distribution_price' => $vv['one_distribution_price'],
                        'two_distribution_price' => $vv['two_distribution_price'],
                        'let_profits_price' => $vv['let_profits_price'],
                        'replace_sell_price' => $vv['replace_sell_price'],
                        'prom_type' => $vv['prom_type'],
                        'prom_id' => $vv['prom_id'],
                        'final_price' => $vv['discount_price'],
                        'deposit' => $vv['deposit'],
                    ];
//                    dump($order_goods);die;
                    Db::name('order_goods')->insert($order_goods);
                }
            }
            $res['status'] = 1;
            $res['msg'] = "提交订单成功";
            if ($orderInfo['total_info']['prom_types'] == 3){
                $res['data'] = ['log_id' => $p_log_id, 'order_sn' => $son_order_sn];
            }else{
                $res['data'] = ['log_id' => $log_id, 'order_sn' => $main_order_sn];
            }
            Db::commit();
        } catch (\Exception $e) {
            $res['status'] = 0;
            $res['msg'] = "提交订单失败," . $e->getMessage();
            Db::rollback();
        }
        return $res;
    }

    /**
     * 检测商品
     * User: Jomlz
     */
    public function goodsSpec($goods_id = '', $key = '', $goods_num = 0 ,$user_id = 0)
    {
        $goodsPromFactory = new GoodsPromFactory();
        $goodsModel = new GoodsModel();
        $field = 'goods_id,goods_sn,goods_name,original_img,market_price,is_on_sale,store_count,prom_type,prom_id,warehouse_id,
        is_free_shipping,template_id,identity,is_member_goods,identity_id,is_del,is_check,one_distribution_price,two_distribution_price,let_profits_price,replace_sell_price';
        $good_info = $goodsModel->field($field)->where(['goods_id' => $goods_id, 'is_on_sale' => 1])->find();
        //商品不存在或者已经下架
        if (empty($good_info) || $good_info['is_on_sale'] != 1 || $good_info['is_del'] == 1 || $good_info['is_check'] != 1) {
            return ['status' => 0, 'msg' => '商品已下架'];
        }
        $good_info = $good_info->toArray();
        //获取商品规格信息
        $goods_spec_price = (new GoodsSpecPriceModel())
            ->with(['specProm'=>function($query){
                $query->where(['status'=>1,'is_del'=>0])->hidden(['status','is_end','add_time','start_time','end_time','is_del','sort','identity','identity_id']);
            }])
            ->where(['goods_id' => $goods_id,'key'=>$key,'prom_id' => $good_info['prom_id'],'is_del'=>0,'is_end'=>0])
            ->append(['spec_price'])
            ->hidden(['bar_code','sku','spec_img','add_time','is_del'])
            ->find();
        if (!$goods_spec_price){
            return ['status'=>0,'msg'=>'商品规格已下架'];
        }
        //查询商品规则库存
        if ($goods_spec_price['store_count'] == 0){
            return ['status'=>0,'msg'=>'已售罄'];
        }
        if ($goods_spec_price['store_count'] < $goods_num) {
            return ['status' => 0, 'msg' => '该规格商品库存不足'];
        }
        //商品的活动是否失效
        if ($goodsPromFactory->checkPromType($good_info['prom_type'])) {
            $goodsPromLogic = $goodsPromFactory->makeModule($good_info,$goods_spec_price);
            if ($goodsPromLogic && !$goodsPromLogic->isAble()) {
                return ['status'=>0,'msg'=>'活动已失效'];
            }
            if ($goodsPromLogic && !$goodsPromLogic->checkActivityIsAble()){
                return ['status'=>0,'msg'=>'活动未开始'];
            }
            if ($goodsPromLogic){
                $info = $goodsPromLogic->getActivityGoodsInfo();
//                echo json_encode($info);die;
            }
        }
        $good_info['price'] = $goods_spec_price['price'];
        $good_info['discount_price'] = $goods_spec_price['price'];
        //如果参加会员折扣价，记录用户等级折扣价格
        if ($good_info['is_member_goods'] == 1){
            if ($user_id > 0){
                $user_info = (new UserModel())->with(['UserGrade'=>function($query){$query->field('id,name,discount');}])->field('id,grade_id')->where(['id'=>$user_id])->find()->toArray();
                $good_info['discount_price'] = round($good_info['price'] * $user_info['UserGrade']['discount'] / 100,2);
            }
        }
        $good_info['deposit'] = $goods_spec_price['deposit'];
        $good_info['item_id'] = $goods_spec_price['item_id'];;
        $good_info['spec_key'] = $key;
        $good_info['spec_key_name'] = $goods_spec_price['key_name'];
        $good_info['cost_price'] = $goods_spec_price['cost_price'];
        $good_info['original_price'] = $goods_spec_price['original_price'];
        $good_info['goods_num'] = $goods_num;
        return ['status' => 1, 'msg' => '成功', 'good_info' => $good_info];
    }

    //订单分组计算运费
    public function orderGroup($goodsList = [], $region_id)
    {
        $FreightLogic = new FreightTemplateLogic();
        $warehouse_array = [];
        $identity_array = [];
        foreach ($goodsList as $item => $value) {
            //把有仓库的分出来，暂时不考虑
//            if($value['warehouse_id'] > 0){
//                $warehouse_array[] = $goodsList[$item];
//            }else{
//                $identity_array[] = $goodsList[$item];
//            }
            $identity_array[] = $goodsList[$item];
        }
        //根据仓库分组计算运费
        $warehouse_array_group = array_group($warehouse_array, 'warehouse_id');
        //去除仓库商品后各身份的分组计算运费
        $identity_array_group = array_group(array_values($identity_array), 'identity_id');
        //根据全部商品身份分组
        $identity_array = array_group(array_values($goodsList), 'identity_id');
        //各身份的商品总价
        $identity_goods_price_array = [];
        //全商品总价
        $goods_total_price = 0;
        foreach ($identity_array as $k => $v) {
            $identity_goods_price = 0;
            $discount_price = 0;
            foreach ($v as $kk => $vv) {
                $identity_goods_price += $vv['price'] * $vv['goods_num'];
                $discount_price += $vv['discount_price'] * $vv['goods_num'];
            }
            $identity_goods_price_array[$k]['identity'] = $vv['identity'];
            $identity_goods_price_array[$k]['identity_id'] = $vv['identity_id'];
            $identity_goods_price_array[$k]['goods_price'] = $identity_goods_price;
            $identity_goods_price_array[$k]['discount_price'] = $discount_price;
//            $goods_total_price += $identity_goods_price;
            $goods_total_price += $discount_price;
        }
        //记录各身份的运费信息
        $total_freight_price = 0;
        $identity_freight_price_array = [];
        foreach ($identity_array_group as $k => $v) {
            $freight_price = $FreightLogic->getFreight($v, $region_id);
            $identity_freight_price_array[$k]['identity'] = $v[0]['identity'];
            $identity_freight_price_array[$k]['identity_id'] = $v[0]['identity_id'];
            $identity_freight_price_array[$k]['freight_price'] = $freight_price;
            $total_freight_price += $freight_price;
        }
        //合并身份下的商品价格跟运费价格
        $identity_id_array = [];
        foreach ($identity_goods_price_array as $k => $v) {
            foreach ($identity_freight_price_array as $kk => $vv) {
                if ($vv['identity'] == $v['identity'] && $vv['identity_id'] == $v['identity_id']) {
                    $identity_goods_price_array[$k]['freight_price'] = $vv['freight_price'];
                    $identity_id_array[] = $identity_goods_price_array[$k];
                }
            }
        }
        return $identity_id_array;
    }

    /**
     * 京东展示订单
     * User: Jomlz
     */
//    public function getOrder($param, $user_id)
//    {
//        $type = $param['type'] ?? 1;
//        $orderModel = new OrderModel();
//        $field = 'id,parent_id,order_sn,order_status,shipping_status,pay_status,comment_status,order_amount,add_time';
//        $order = 'add_time desc';
//        $where2 = ['user_id' => $user_id, 'is_del' => 0, 'pay_status' => 0, 'parent_id' => 0];
//        switch ($type) {
//            case 1: //已支付
//                $where = [['user_id', '=', $user_id], ['is_del', '=', 0], ['pay_status', '=', 1], ['parent_id', '<>', 0]];
//                break;
//            case 2: //待支付
//                $where2 = [['user_id', '=', $user_id], ['is_del', '=', 0], ['pay_status', '=', 0], ['parent_id', '<>', 0]];
//                break;
//            case 3: //待发货
//                $where = [['user_id', '=', $user_id], ['is_del', '=', 0], ['pay_status', '=', 1], ['shipping_status', '<>', 1], ['parent_id', '<>', 0]];
//                break;
//            case 4: //待收货
//                $where = [['user_id', '=', $user_id], ['is_del', '=', 0], ['pay_status', '=', 1], ['shipping_status', '=', 1], ['parent_id', '<>', 0]];
//                break;
//            case 5: //退款售后
//                break;
//            case 6: //已收货
//                $where = [['user_id', '=', $user_id], ['is_del', '=', 0], ['pay_status', '=', 1], ['order_status', '=', 2], ['parent_id', '<>', 0]];
//                break;
//        }
//        $lists_2 = [];
//        $lists = [];
//        if ($type == 1 || $type == 2){
//            $lists = $orderModel->with(['orderSon', 'orderSon.orderGoods', 'orderSon.orderGoods.GoodsInfo'])
//                ->field($field)
//                ->where($where2)
//                ->append(['status_text', 'add_time_data'])
//                ->order($order)
//                ->select()->toArray();
////            dump($lists);die;
//            foreach ($lists as $orderKey => $orderVal) {
//                $goodsImgs = [];
//                $goodsNum = 0;
//                foreach ($orderVal['orderSon'] as $kk => $vv) {
//                    foreach ($vv['orderGoods'] as $goodkey => $goodsval) {
//                        $goodsNum += $goodsval['goods_num'];
//                        $original_img = $goodsval['GoodsInfo'];
//                        array_push($goodsImgs, $original_img);
//                    }
//                }
//                $lists[$orderKey]['goods_num'] = $goodsNum;
//                $lists[$orderKey]['goods_imgs'] = $goodsImgs;
//                unset($lists[$orderKey]['orderSon']);
//            }
//        }
//        if ($type != 2){
//            $lists_2 = $orderModel->with(['orderGoods','orderGoods.GoodsInfo'])
//                ->field($field)
//                ->where($where)
//                ->append(['status_text', 'add_time_data'])
//                ->order($order)
//                ->select()->toArray();
//            foreach ($lists_2 as $orderKey => $orderVal) {
//                $goodsImgs = [];
//                $goodsNum = 0;
//                foreach ($orderVal['orderGoods'] as $goodkey => $goodsval) {
//                    $goodsNum += $goodsval['goods_num'];
//                    $original_img = $goodsval['GoodsInfo'];
//                    array_push($goodsImgs, $original_img);
//                }
//                $lists_2[$orderKey]['goods_num'] = $goodsNum;
//                $lists_2[$orderKey]['goods_imgs'] = $goodsImgs;
//                unset($lists_2[$orderKey]['orderGoods']);
//            }
//        }
//        $data = array_merge($lists,$lists_2);
//       return $data;
//    }

    public function getOrder($param, $user_id)
    {
        $type = $param['type'] ?? 1;
        $orderModel = new OrderModel();
        $paramWhere = $param['where'] ?? [];
        $field = 'id,parent_id,identity_id,order_sn,order_status,shipping_status,shipping_price,pay_status,comment_status,order_amount,actual_amount,add_time,prom_types,goods_num,confirm_time';
        $order = 'add_time desc';
        switch ($type) {
            case 1: //全部
                $where = [];
                break;
            case 2: //待支付
                $where = [['pay_status', '=', 0]];
                break;
            case 3: //待发货
                $where = [['pay_status', '=', 1], ['shipping_status', '<>', 1]];
                break;
            case 4: //待收货
                $where = [['pay_status', '=', 1], ['shipping_status', '=', 1],['confirm_time', '=', 0],['comment_status','=',0]];
                break;
                break;
            case 5: //待评价
                $where = [['pay_status', '=', 1], ['order_status', '=', 2], ['confirm_time', '>', 0],['confirm_time','>',0],['comment_status', '<>', 1]];
                break;
        }
        $lists = $orderModel
            ->with(['identity', 'orderGoods', 'orderGoods.GoodsInfo','payLog'])
            ->field($field)
            ->where([['parent_id','<>',0],['user_id', '=', $user_id],['is_del', '=', 0]])
            ->where($where)
            ->where($paramWhere)
            ->append(['status_text', 'add_time_data'])
            ->hidden(['add_time','comment_status','shipping_status','order_status','identity_id'])
            ->order($order)
            ->select()->toArray();
        $data['lists'] = $lists;
        return $data;
    }

    /**
     * 获取订单详情
     * User: Jomlz
     */
    public function getDetails($param, $user_id)
    {
        $where = [['user_id', '=', $user_id], ['id', '=', $param['id']], ['is_del', '=', 0], ['parent_id', '<>', 0]];
        $paramWhere = $param['where'] ?? [];
        $info = (new OrderModel())
            ->with(['identity', 'orderGoods', 'orderGoods.GoodsInfo','payLog','promInfo'])
            ->where($where)
            ->where($paramWhere)
            ->append(['status_text', 'add_time_data', 'full_address', 'pay_time_date'])
            ->find();
        if (empty($info)) {
            return JsonUtils::fail('信息不存在');
        }
        $info = $info->toArray();
        $user_address = array(
            'consignee' => $info['consignee'],
            'mobile' => $info['receiver_tel'],
            'address' => $info['full_address'],
        );
        $order_info = [
            'order_sn' => $info['order_sn'],
            'add_time' => $info['add_time_data'],
            'goods_price' => $info['goods_price'],
            'shipping_price' => $info['shipping_price'],
            'coupon_price' => $info['coupon_price'],
            'integral_money' => $info['integral_money'],
            'paid_money' => $info['paid_money'],
            'last_money' => $info['goods_price'] - $info['paid_money'],
            'order_amount' => $info['order_amount'],
            'pay_time' => $info['pay_time_date'],
        ];
        $prom_info = [];
        if ($info['promInfo']){
            switch ($info['prom_types']){
                case 3: //预售
                    $prom_info = [
                        'type' => $info['promInfo']['type'],
                        'title' => $info['promInfo']['title'],
                        'final_payment_start_time' => $info['promInfo']['final_payment_start_time_data'],
                        'final_payment_end_time' => $info['promInfo']['final_payment_end_time_data'],
                    ];
                    break;
            }
        }
        $response['status_text'] = $info['status_text'];
        $response['prom_info'] = $prom_info;
        $response['user_address'] = $user_address;
        $response['identity_info'] = $info['identity'];
        $response['goods_lists'] = $info['orderGoods'];
        $response['order_info'] = $order_info;
        $response['log_info'] = $info['payLog'];
        return JsonUtils::successful('获取成功', $response);
    }

    /**
     * 订单商品信息
     * User: Jomlz
     */
    public function getOrderGoods($param, $user_id)
    {
        $field = 'o.id,o.order_sn,o.add_time,og.rec_id,og.goods_name,og.spec_key_name,og.goods_num,og.goods_price,og.final_price,au.nickname,g.original_img';
        $order_info = Db::name('order')->alias('o')
            ->field($field)
            ->where(['o.user_id' => $user_id, 'o.is_del' => 0, 'o.id' => $param['id']])
            ->where([['o.parent_id', '<>', 0], ['og.rec_id', '=', $param['rec_id']]])
            ->join('order_goods og', 'og.order_id = o.id')
            ->join('admin_users au', 'au.id = o.identity_id')
            ->join('goods g', 'g.goods_id = og.goods_id')
            ->find();
        if (!$order_info) {
            return JsonUtils::fail('订单错误');
        }
        $order_info['add_time'] = date('Y-m-d H:i;s', $order_info['add_time']);
        $order_info['goods_price'] = $order_info['goods_price'] * $order_info['goods_num'];
        $order_info['final_price'] = $order_info['final_price'] * $order_info['goods_num'];
        $response['order_goods_info'] = $order_info;
        return JsonUtils::successful('获取成功', $response);
    }

    /**
     * 修改订单信息
     * User: Jomlz
     */
    public function orderSave($param, $user_id)
    {
        $where = $param['where'] ?? [];
        $data = $param['data'] ?? [];
        $order_info = (new OrderModel())
            ->where(array('user_id' => $user_id, 'is_del' => 0, 'id' => $param['id']))
            ->where('parent_id', '<>', 0)
            ->where($where)
            ->find();
        if (!$order_info) {
            return JsonUtils::fail('订单错误');
        }
        $res = (new OrderModel())->where(['id' => $param['id']])->save($data);
        if ($res) {
            return JsonUtils::successful('成功');
        } else {
            return JsonUtils::fail('失败');
        }
    }

    /**
     * 物流信息
     */
    public function logisticsInfo($param, $user_id)
    {
        $field = 'o.id,o.shipping_status,g.rec_id,g.is_deliver,g.delivery_id';
        $order_info = Db::name('order')->alias('o')
            ->field($field)
            ->where(['o.user_id' => $user_id, 'o.is_del' => 0, 'o.id' => $param['id']])
            ->where([['o.parent_id', '<>', 0], ['o.shipping_status', '<>', 0], ['g.rec_id', '=', $param['rec_id']], ['g.is_deliver', '=', 1]])
            ->join('order_goods g', 'g.order_id = o.id')
            ->find();
        if (!$order_info) {
            return JsonUtils::fail('订单错误');
        }
        $delivery = Db::name('order_delivery')
            ->field('shipping_code,shipping_name,invoice_no,add_time')
            ->where(['id' => $order_info['delivery_id']])
            ->find();
        $k_arr = kuaidi100('yunda', $delivery['invoice_no']);
        $k_arr = json_decode($k_arr, true);
        $logistics_info = [];
        if ($k_arr['message'] == 'ok') {
            foreach ($k_arr['data'] as $key => $val) {
                $re = array(
                    'time' => $val['time'],
                    'context' => $val['context'],
                );
                array_push($logistics_info, $re);
            }
        } else {
            return JsonUtils::fail($k_arr['message']);
        }
        $logistics_default = [];
        $re1 = array(
            'time' => date('Y-m-d H:i:s', $delivery['add_time']),
            'context' => '您的订单平台拣货完毕，待出库交付' . $delivery['shipping_name'] . '快递，' . '运单号为' . $delivery['invoice_no'],
        );
        array_push($logistics_default, $re1);
        $logistics_info = array_merge($logistics_info, $logistics_default);
        $data['lists'] = $logistics_info;
        return JsonUtils::successful('获取成功', $data);
    }

    /**
     * 评论改变订单信息
     * User: Jomlz
     */
    public function changeOrderComment($user_id=0, $order_id=0, $rec_id=0)
    {
        if (!$user_id || !$order_id || !$rec_id){
            return ['status' => 0, 'msg' => '缺少参数'];
        }
        $orderModel = new OrderGoodsModel();
        $order_info = $orderModel->where([['user_id', '=', $user_id], ['id', '=', $order_id], ['parent_id', '<>', 0], ['pay_status', '<>', 0]])->find();
        $order_goods_info = $orderModel->where([['order_id', '=', $order_id], ['rec_id', '=', $rec_id], ['is_comment', '=', 0], ['is_deliver', '<>', 0]])->find();
        if (!$order_info || !$order_goods_info) {
            return ['status' => 0, 'msg' => '参数错误或不可评论'];
        }
        Db::startTrans();
        try {
            (new OrderGoodsModel())->where(['rec_id' => $rec_id])->save(['is_comment' => 1, 'comment_time' => time()]);
            $query = (new OrderGoodsModel())->field('rec_id')->where(['is_comment' => 0, 'order_id' => $order_id])->find();
            if (!$query) {
                $orderModel->where(['id' => $order_id])->save(['comment_status' => 1, 'order_status' => 4]);
            } else {
                $orderModel->where(['id' => $order_id])->save(['comment_status' => 2]);
            }
            Db::commit();
            return ['status' => 1, 'msg' => '操作成功'];
        } catch (\Exception $e) {
            Db::rollback();
            return ['status' => 0, 'msg' => '操作失败'];
        }
    }

}