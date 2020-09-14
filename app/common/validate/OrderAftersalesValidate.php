<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/17
 * Time: 11:49
 */

namespace app\common\validate;

use think\Validate;

class OrderAftersalesValidate extends Validate
{
    protected $rule = [
        'id|售后订单ID' => 'require',
        'btn_status|操作参数btn_status' => 'require|number|gt:0|checkStatus',
        'order_id|订单id' => 'require|number|gt:0',
        'rec_id|订单商品id' => 'require|number|gt:0',
        'goods_num|商品数量' => 'require|number|gt:0',
        'aftersales_type|退款类型' => 'require|number|gt:0',
        'reason|原因' => 'require',
        'evidence_pic|拍照凭证' => 'require',
        'express_name|快递名称' => 'require',
        'express_sn|快递单号' => 'require',
    ];

    protected $message = [

    ];

    protected $scene = [
        'info' => ['id'],
        'handle' => ['id','btn_status'],
        'apply' => ['order_id','rec_id','goods_num','aftersales_type','reason'],
        'user_delivery' => ['id','express_sn','express_name'],
        'cancel' => ['id'],
    ];

    protected function checkStatus($value,$rule,$data)
    {
      $btn_status = $data['btn_status'];
      switch ($btn_status){
          case 5:
              if (!isset($data['seller_delivery']) || empty($data['seller_delivery'])){
                  return '发货信息不能为空';
              }
              $seller_delivery = json_decode($data['seller_delivery'],true);
              if (!isset($seller_delivery['express_name']) || !isset($seller_delivery['express_sn']) || empty($seller_delivery['express_sn']) || empty($seller_delivery['express_sn'])){
                  return '快递名称或快递单号不能为空';
              }
              break;
      }
        return true;
    }

}