<?php
/**
 * Created by PhpStorm.
 * PHP version 版本号
 *
 * @category 类别名称
 * @package  暂无
 * @author   hj <138610033@qq.com>
 * @license  暂无
 * @link     暂无
 * DateTime: 2020/8/19 上午11:48
 */

namespace app\common\model;

class ActivityModel extends CommonModel
{
    protected $name = 'activity';

    public $searchField = [
        'title',
        'status'
    ];

    protected $whereField = [
        'type',
        'start_time'
    ];

    protected function getStartTimeDataAttr($value,$data)
    {
        return date('Y-m-d H:i:s',$data['start_time']);
    }

    protected function getEndTimeDataAttr($value,$data)
    {
        return date('Y-m-d H:i:s',$data['end_time']);
    }

    protected function getFinalPaymentStartTimeDataAttr($value,$data)
    {
        return date('Y-m-d H:i:s',$data['final_payment_start_time']);
    }

    protected function getFinalPaymentEndTimeDataAttr($value,$data)
    {
        return date('Y-m-d H:i:s',$data['final_payment_end_time']);
    }

    protected function getStatusTextAttr($value,$data)
    {
        $arr = [
            '0' => '未开启',
            '1' => '进行中',
            '2' => '已过期',
        ];
        return $arr[$data['status']];
    }


    /**
     * 活动商品详情提示
     * 活动提示状态：1未开始 2活动结束 3活动进行中 4商品售罄
     * User: Jomlz
     */
    protected function getPromTipAttr($value,$data)
    {
        $start_time = $data['start_time'];
        $end_time = $data['end_time'];
        $prom_tip = ['prom_status' => 0, 'prom_msg' => '未知'];
        if (time() < $start_time) {
            $prom_tip = ['prom_status' => 1, 'prom_msg' => '未开始'];
        }
        if (time() > $start_time && time() < $end_time) {
            if ($data['goods_num'] > $data['buy_num']) {
                $also_num = $data['goods_num'] - $data['buy_num'];
                switch ($data['type']) {
                    case 2: //秒杀
                        $prom_tip = ['prom_status' => 3, 'prom_msg' => "秒杀进行中，仅剩" . $also_num . '件'];
                        break;
                    case 3: //预售
                        $prom_tip = ['prom_status' => 5, 'prom_msg' =>'预售进行中'];
                        break;
                }
            } else {
                $prom_tip = ['prom_status' => 4, 'prom_msg' => '该商品已售罄'];
            }
        }
        if (time() > $end_time) {
            $prom_tip = ['prom_status' => 2, 'prom_msg' => '活动已结束'];
        }

        return $prom_tip;
    }

    public function priceText($type)
    {
        $data = [
            1 => 'group',
            2 => 'seckill',
            3 => 'pre_sale',
            4 => 'rush'
        ];
        return $data[$type];
    }

}