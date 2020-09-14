<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/17
 * Time: 19:24
 */

namespace app\common\model\order;

use app\apiadmin\model\AdminUsers;
use app\common\model\CommonModel;
use app\common\model\user\UserModel;

class OrderAftersalesModel extends CommonModel
{
    protected $name = 'order_aftersales';

    //可搜索字段
    protected $searchField = [
        'order_sn',
    ];

    //可作为条件的字段
    protected $whereField = [
        'identity',
        'identity_id',
        'warehouse_id',
        'status',
        'aftersales_type',
    ];

    public function orderInfo()
    {
        return $this->hasOne(OrderModel::class,'id','order_id')
            ->field('id,identity,identity_id,order_sn,user_id,pay_time,order_status,pay_status,pay_type,shipping_price,shipping_status,consignee,
            province,city,county,twon,address,zip,user_note,receiver_tel,add_time,integral_money,coupon_price,user_money,actual_amount')
            ->append(['order_status_text','pay_status_text','shipping_status_text','add_time_data','pay_time_date','pay_type_text','full_address','identity_text']);
    }

    public function orderGoods()
    {
        return $this->hasOne('OrderGoodsModel','rec_id','rec_id')
            ->field('rec_id,identity,identity_id,goods_id,goods_sn,final_price,goods_name,goods_price,spec_key,spec_key_name,is_deliver,readjust_price');
    }

    protected function getAddTimeDateAttr($value,$data)
    {
        return date('Y-m-d H:i:s',$data['add_time']) ?? '';
    }
    public function getAftersalesTypeTextAttr($value,$data)
    {
        return config('status')['AFTERSALES_TYPE'][$data['aftersales_type']] ?? '';
    }
    public function getStatusTextAttr($value,$data)
    {
        return config('status')['AFTERSALES_STATUS'][$data['status']] ?? '';
    }

    public function adminUser()
    {
        return $this->hasOne(AdminUsers::class,'id','identity_id')->field('id,nickname');
    }
    public function identity()
    {
        return $this->hasOne(AdminUsers::class,'id','identity_id')->field('id,identity,nickname');
    }

    public function getIdentityTextAttr($value,$data)
    {
        return config('status')['IDENTITY'][$data['identity']] ?? '';
    }

    public function user()
    {
        return $this->hasOne(UserModel::class,'id','user_id')->field('id,nick_name,username');
    }

    //用户发货信息
    public function getUserDeliveryInfoAttr($value,$data)
    {
       $user_delivery = unserialize($data['user_delivery']);
       if ($user_delivery){
           $user_delivery['express_time'] = date('Y-m-d H:i:s',$user_delivery['express_time']) ?? '';
       }else{
           $user_delivery = (object)[];
       }
       return $user_delivery;
    }
    //卖家重新发货地址
    public function getSellerDeliveryInfoAttr($value,$data)
    {
        $seller_delivery = unserialize($data['seller_delivery']);
        if ($seller_delivery){
            $seller_delivery['express_time'] = date('Y-m-d H:i:s',$seller_delivery['express_time']) ?? '';
        }else{
            $seller_delivery = (object)[];
        }
        return $seller_delivery;
    }
    //图片凭证信息
    public function getEvidencePicAttr($value,$data)
    {
        if ($data['evidence_pic']){
            $pics = explode(',',$data['evidence_pic']);
            foreach ($pics as $k=>$v){
                $pics_arr[$k]['img_url'] = $v;
            }
        }else{
            $pics_arr = [];
        }
        return $pics_arr;
    }

    /**
     * 售后可执行操作按钮(后台)
     * 操作按钮汇总 ：1申请通过 2申请不通过 3支付原路退回 4退回到用户余额 5重新发货 6商家确认收货
     * @param $value
     * @param $data
     * @return array
     * User: Jomlz
     */
    public function getAfterButtonAttr($value,$data)
    {
        $status = $data['status'];//售后订单状态
        $type = $data['aftersales_type'];//售后类型 1-仅退款, 2-退货退款,3-换货',
        $is_receive = $data['is_receive'];//申请售后时是否收到货物,
        $btn_arr = [];
        if ($status == 0){
            $btn_arr[] = ['btn_status'=>1,'btn_name'=>'申请通过'];
            $btn_arr[] = ['btn_status'=>2,'btn_name'=>'申请不通过'];
        }
        if ($type > 1){
//            if ($status == 1 && $is_receive == 0){
//                $btn_arr[] = ['btn_status'=>0,'btn_name'=>'买家发货'];
//            }
            if ($status == 2 && $is_receive == 0){
                $btn_arr[] = ['btn_status'=>6,'btn_name'=>'商家确认收货'];
            }
        }
        if ($status == 1 || $status == 2){
            if ($type == 1 || ($type == 2 && $status == 2 && $is_receive == 1)){
                $btn_arr[] = ['btn_status'=>3,'btn_name'=>'支付原路退回'];
                $btn_arr[] = ['btn_status'=>4,'btn_name'=>'退回到用户余额'];
            }
        }
        //换货
        if ($type == 3 && $status == 2  && $is_receive == 1){
            $btn_arr[] = ['btn_status'=>5,'btn_name'=>'重新发货'];
        }
        return $btn_arr;
    }

    /**
     * 1待审核 2退款中 3待退货 4买家已发货 5退款完成 6商家已发货 7换货完成
     * User: Jomlz
     * Date: 2020/8/25 17:53
     */
    protected function getStatusReminderAttr($vaule,$data)
    {
        $status = $data['status'];//售后订单状态
        $type = $data['aftersales_type'];//售后类型 1-仅退款, 2-退货退款,3-换货',
        $sr_arr = [];
        if ($status == 0){
            $sr_arr = ['sr_status'=>1,'sr_name'=>'待审核'];
        }
        if ($type == 1 && $status == 1){
            $sr_arr = ['sr_status'=>2,'sr_name'=>'退款中'];
        }
        if (($type == 2 || $type == 3) && $status == 1){
            $sr_arr = ['sr_status'=>3,'sr_name'=>'待退货'];
        }
        if (($type == 2 || $type == 3) && $status == 2){
            $sr_arr = ['sr_status'=>4,'sr_name'=>'买家已发货'];
        }
        if ($status == 5){
            $sr_arr = ['sr_status'=>5,'sr_name'=>'退款完成'];
        }
        if ($type == 3){
            if ($status == 3){
                $sr_arr = ['sr_status'=>6,'sr_name'=>'商家已发货'];
            }
            if ($status == 4){
                $sr_arr = ['sr_status'=>7,'sr_name'=>'换货完成'];
            }
        }
        return $sr_arr;
    }
}