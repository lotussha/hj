<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/14
 * Time: 14:40
 */

namespace app\common\model\order;

use app\apiadmin\model\AdminUsers;
use app\common\model\ActivityModel;
use app\common\model\CommonModel;
use app\common\model\PayLogModel;
use app\common\model\user\UserModel;
use think\facade\Db;

class OrderModel extends CommonModel
{
    protected $name = 'order';

    //可搜索字段
    protected $searchField = [
        'order_sn',
        'consignee',
    ];
    //可作为条件的字段
    protected $whereField = [
        'identity',
        'identity_id',
        'warehouse_id',
        'order_status',
        'pay_type',
        'pay_status',
        'shipping_status',
        'prom_type',
    ];

    //可字段搜索器 时间范围查询
    protected $timeField = [
        'add_time',
        'pay_time',
    ];

    protected function getAddTimeDataAttr($value,$data)
    {
        return date('Y-m-d H:i:s',$data['add_time']);
    }
    protected function getPayTimeDateAttr($value,$data)
    {
        return $data['pay_time'] ? date('Y-m-d H:i:s',$data['pay_time']) : '';
    }

    protected function getPayTypeTextAttr($value,$data)
    {
        return config('status')['PAY_TYPE'][$data['pay_type']] ?? '其他方式';
    }

    protected function getShippingtypeTextAttr($value,$data)
    {
        return config('status')['SHIPPING_TYPE'][$data['shipping_type']] ?? '其他方式';
    }

    protected function getOrderStatusTextAttr($value,$data)
    {
        return config('status')['ORDER_STATUS'][$data['order_status']] ?? '未知';
    }
    protected function getShippingStatusTextAttr($value,$data)
    {
        return config('status')['SHIPPING_STATUS'][$data['shipping_status']] ?? '未知';
    }
    protected function getPayStatusTextAttr($value,$data)
    {
        return config('status')['PAY_STATUS'][$data['pay_status']] ?? '未知';
    }

    public function getIdentityTextAttr($value,$data)
    {
        return config('status')['IDENTITY'][$data['identity']] ?? '主订单';
    }

    public function adminUser()
    {
        return $this->hasOne(AdminUsers::class,'id','identity_id')->field('id,nickname');
    }

    public function identity()
    {
        return $this->hasOne(AdminUsers::class,'id','identity_id')->field('id,identity,nickname');
    }

    //关联父订单
    public function orderParent()
    {
        return $this->hasOne('OrderModel','id','parent_id')
            ->field('id,parent_id,user_id,order_sn,pay_type,receiver_consignee,goods_price,shipping_type,order_amount,add_time,order_status,shipping_status,pay_status');
    }
    //关联子订单
    public function orderSon()
    {
        return $this->hasMany('OrderModel','parent_id','id')
            ->field('id,parent_id,order_sn,identity,identity_id,pay_status,
            shipping_status,goods_price,shipping_price,order_amount,actual_amount,shipping_type,user_note,admin_note');
    }
    //关联订单商品
    public function orderGoods()
    {
        return $this->hasMany(OrderGoodsModel::class,'order_id','id')
            ->field('rec_id,order_id,goods_id,goods_name,goods_sn,goods_num,goods_price,readjust_price,final_price,give_integral,discount_price,spec_key_name,is_deliver,delivery_id')
            ->append(['subtotal_price']);
    }
    //关联发货单
    public function delivery()
    {
        return $this->hasMany(OrderDelivery::class,'order_id','id');
    }
    //关联支付记录
    public function payLog()
    {
        return $this->hasOne(PayLogModel::class,'order_id','id')->where(['pay_type'=>1,'is_pay'=>0])->field('log_id,order_sn,order_id,pay_amount,is_pay');
    }
    //关联活动信息
    public function promInfo()
    {
        return $this->hasOne(ActivityModel::class,'id','prom_ids')->append(['start_time_data','end_time_data','final_payment_start_time_data','final_payment_end_time_data']);
    }

    //关联用户
    public function user()
    {
        return $this->hasOne(UserModel::class,'id','user_id')->field('id,username,nick_name');
    }
    /**
     * 订单详细收货地址
     * @param $value
     * @param $data
     * @return string
     */
    public function getFullAddressAttr($value, $data)
    {
        $province = Db::name('region')->where(['id'=>$data['province']])->value('name');
        $city = Db::name('region')->where(['id'=>$data['city']])->value('name');
        $county = Db::name('region')->where(['id'=>$data['county']])->value('name');
        $twon = Db::name('region')->where(['id'=>$data['twon']])->value('name');
        $address = $province . $city . $county . $twon .$data['address'];
//        $address = Db::name('region')->where(['id'=>$data['twon']])->value('merger_name');
        return $address;
    }

    /**
     * 获取当前可操作的按钮(后台)
     * 操作按钮汇总 ：1确认、2去发货、3取消确认、4付款、5设为未付款、6申请退货、7作废、8确认收货（去除）
     * User: Jomlz
     * Date: 2020/8/18 9:55
     */
    public function getOrderButtonAttr($value, $data)
    {
        $os = $data['order_status'];//订单状态
        $ss = $data['shipping_status'];//发货状态
        $ps = $data['pay_status'];//支付状态
        $pt = $data['prom_types'];//订单类型：0默认1抢购2团购3优惠4预售5拼团
        $btn_arr = [];
        if($ps == 0 && $os == 0 || $ps == 2){
            $btn_arr[] = ['btn_status'=>4,'btn_name'=>'付款'];
        }elseif($os == 0 && $ps == 1){
            if($pt != 5){
                $btn_arr[] = ['btn_status'=>1,'btn_name'=>'确认'];
                $btn_arr[] = ['btn_status'=>5,'btn_name'=>'设为未付款'];
            }
        }elseif($os == 1 && $ps == 1 && ($ss == 0 || $ss == 2)){
            if($pt != 5){
                $btn_arr[] = ['btn_status'=>3,'btn_name'=>'取消确认'];
            }
            $btn_arr[] = ['btn_status'=>2,'btn_name'=>'去发货'];
        }
        if($ss == 1 && $os == 1 && $ps == 1){
//            $btn_arr[] = ['btn_status'=>8,'btn_name'=>'确认收货'];
            $btn_arr[] = ['btn_status'=>6,'btn_name'=>'申请退货'];
        }elseif($os == 2 || $os == 4){
            $btn_arr[] = ['btn_status'=>6,'btn_name'=>'申请退货'];
        }
        if($os != 5){
            $btn_arr[] = ['btn_status'=>7,'btn_name'=>'作废'];
        }
        return $btn_arr;
    }

    /**
     * 获取当前可操作的按钮(前台)
     * 订单状态 0：未知状态 1：待支付 2：待发货 3：已取消 4：待收货 5：待评价 6：已完成
     * 7：待支付尾款
     */
    protected function getStatusTextAttr($vaule,$data)
    {
        $os = $data['order_status'];//订单状态
        $ss = $data['shipping_status'];//发货状态
        $ps = $data['pay_status'];//支付状态c
        $cs = $data['comment_status'];//评论状态
        $ct = $data['confirm_time'];//收货时间
        $pt = $data['prom_types'];
        $btn_arr = ['status'=>0,'reminder'=>'未知状态'];
        if($ps == 0 && $os == 0){
            $btn_arr = ['status'=>1,'reminder'=>'待支付'];
        }
        if ($pt == 3){
//            if ($ps == 0){
//                $btn_arr = ['status'=>7,'reminder'=>'待支付定金'];
//            }
            if ($ps == 2){
                $btn_arr = ['status'=>8,'reminder'=>'待支付尾款'];
            }
        }
        if ($ps == 1 && ($ss == 0 || $ss == 2)){
            $btn_arr = ['status'=>2,'reminder'=>'待发货'];
        }
        if ($ps == 0 && $os == 3){
            $btn_arr = ['status'=>3,'reminder'=>'已取消'];
        }
        if ($ps == 1 && $os != 2 && $ss == 1 && $ct == 0){
            $btn_arr = ['status'=>4,'reminder'=>'待收货'];
        }
        if ($ps == 1 && $ss == 1 && $os == 2 && $cs != 1 && $ct > 0){
            $btn_arr = ['status'=>5,'reminder'=>'待评价'];
        }
        if ($cs == 1 && $os == 4){
            $btn_arr = ['status'=>6,'reminder'=>'交易完成'];
        }
        return $btn_arr;
    }



}