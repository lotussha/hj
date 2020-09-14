<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/12
 * Time: 16:49
 */

namespace app\apiadmin\controller;

use app\common\logic\FreightTemplateLogic;
use app\common\model\GoodsModel;
use app\common\model\order\OrderModel;
use sakuno\utils\JsonUtils;
use think\facade\Db;

class Test extends Base
{
    /**
     * 运费计算
     * User: Jomlz
     * Date: 2020/8/12 16:51
     */
    public function freight_calculation()
    {
        $arr = array_return();
        $FreightLogic = new FreightTemplateLogic();
        //商品数组
        $lists = [
            [
                'goods_id' => $this->param['goods_id'],
                'goods_num' => $this->param['goods_num'],
            ],
            [
            'goods_id' => 39,
            'goods_num' => 1,
                ],
            [
            'goods_id' => 40,
            'goods_num' => 2,
                ],
        ];
        //收货地址id
        $region_id = $this->param['region_id'] ?? '13402';
        $freight_price = $FreightLogic->getFreight($lists,$region_id);
        $arr['data'] = ['freight_price'=>$freight_price];
        return return_json($arr);
    }

    public function order_test()
    {
        $res = array_return();
        $FreightLogic = new FreightTemplateLogic();
        $Goods = new GoodsModel();
        //商品
        $goodsArr =  [
            ['goods_id'=>1,'goods_num'=>2],
            ['goods_id'=>39,'goods_num'=>1],
            ['goods_id'=>40,'goods_num'=>1],
            ['goods_id'=>41,'goods_num'=>5],
            ['goods_id'=>42,'goods_num'=>2],
            ['goods_id'=>43,'goods_num'=>1],
        ];
        dump(json_decode($this->param['goods_arr'],true));die;
        //收货地址
        $region_id = $this->param['region_id'] ?? '13402';
        $goods_ids = get_arr_column($goodsArr, 'goods_id');
        $goodsList = $Goods
            ->field('goods_id,goods_name,goods_sn,shop_price,cost_price,volume,weight,template_id,is_free_shipping,identity,identity_id,warehouse_id')
            ->where('goods_id', 'IN', $goods_ids)->select()->toArray();
        //把商品数量加进去
        $warehouse_array = [];
        $identity_array = [];
        foreach ($goodsList as $item=>$value){
            foreach ($goodsArr as $tt=>$vv){
                if ($value['goods_id'] == $vv['goods_id']) {
                    $goodsList[$item]['goods_num'] = $vv['goods_num'];
                }
            }
            //把有仓库的分出来
            if($value['warehouse_id'] > 0){
                $warehouse_array[] = $goodsList[$item];
            }else{
                $identity_array[] = $goodsList[$item];
            }
        }
        //根据仓库分组计算运费
        $warehouse_array_group = array_group($warehouse_array,'warehouse_id');
        //去除仓库商品后各身份的分组计算运费
        $identity_array_group = array_group(array_values($identity_array),'identity_id');
        //根据全部商品身份分组
        $identity_array = array_group(array_values($goodsList),'identity_id');
        //各身份的商品总价
        $identity_goods_price_array = [];
        //全商品总价
        $goods_total_price = 0;
        foreach ($identity_array as $k=>$v){
            $identity_goods_price = 0;
            foreach ($v as $kk=>$vv){
                $identity_goods_price += $vv['shop_price'] * $vv['goods_num'];
            }
            $identity_goods_price_array[$k]['identity'] = $vv['identity'];
            $identity_goods_price_array[$k]['identity_id'] = $vv['identity_id'];
            $identity_goods_price_array[$k]['goods_price'] = $identity_goods_price;
            $goods_total_price += $identity_goods_price;
        }
        //记录各身份的运费信息
        $total_freight_price = 0;
        $identity_freight_price_array = [];
        foreach ($identity_array_group as $k=>$v){
            $freight_price = $FreightLogic->getFreight($v,$region_id);
            $identity_freight_price_array[$k]['identity'] = $v[0]['identity'];
            $identity_freight_price_array[$k]['identity_id'] = $v[0]['identity_id'];
            $identity_freight_price_array[$k]['freight_price'] = $freight_price;
            $total_freight_price += $freight_price;
        }
        //合并身份下的商品价格跟运费价格
        $identity_id_array = [];
        foreach ($identity_goods_price_array as $k=>$v){
            foreach ($identity_freight_price_array as $kk=>$vv){
                if ($vv['identity'] == $v['identity'] && $vv['identity_id'] == $v['identity_id']){
                    $identity_goods_price_array[$k]['freight_price'] = $vv['freight_price'];
                    $identity_id_array[] = $identity_goods_price_array[$k];
                }
            }
        }
//        dump($identity_id_array);
//        dump($goodsList);die;
        //保存主订单
        $order_sn = mt_rand(1000000000000,9999999999999);
        $order_data = [
            'user_id' => 1,
            'order_sn' => $order_sn,
            'identity' => '',
            'identity_id' =>'',
            'warehouse_id' =>'',
            'goods_price' =>$goods_total_price,
            'order_amount' =>$goods_total_price,
            'shipping_price' =>$total_freight_price,
            'add_time' => time(),
        ];
        // 启动事务
        Db::startTrans();
        try {
            $order_id = Db::name('order')->insertGetId($order_data);
            //根据身份分组保存身份子订单
            foreach ($identity_id_array as $k=>$v)
            {
                $identity_order_sn = mt_rand(1000000000000,9999999999999);
                $identity_order = [
                    'parent_id' => $order_id,
                    'user_id' => 1,
                    'order_sn' => $identity_order_sn,
                    'identity' => $v['identity'],
                    'identity_id' => $v['identity_id'],
                    'goods_price' =>$v['goods_price'],
                    'order_amount' =>$v['goods_price'],
                    'shipping_price' => $v['freight_price'],
                    'add_time' => time(),
                ];
                $order_parent_id = Db::name('order')->insertGetId($identity_order);
                //根据商品保存订单商品
                foreach ($goodsList as $kk=>$vv){
                    if ($vv['identity'] == $v['identity'] && $vv['identity_id'] == $v['identity_id']){
                        $order_goods = [
                            'order_id'=>$order_parent_id,
                            'order_parent_id'=>$order_id,
                            'order_sn'=>$identity_order_sn,
                            'goods_id'=>$vv['goods_id'],
                            'goods_name'=>$vv['goods_name'],
                            'goods_sn'=>$vv['goods_sn'],
                            'goods_num'=>$vv['goods_num'],
                            'goods_price'=>$vv['shop_price'],
                            'cost_price'=>$vv['cost_price'],
                            'identity'=>$vv['identity'],
                            'identity_id'=>$vv['identity_id'],
                            'spec_key'=>'',
                            'spec_key_name'=>'',
                        ];
                        Db::name('order_goods')->insert($order_goods);
                    }
                }
            }

            $res['status'] = 1;
            $res['msg'] = "提交订单成功";
            Db::commit();
        }catch (\Exception $e){
            $res['status'] = 0;
            $res['msg'] = "提交订单失败," . $e->getMessage();
            Db::rollback();
        }
        return return_json($res);
    }

    public function kuaidi100()
    {
        $k_arr = kuaidi100('yunda', '3102939550507');
        $k_arr = json_decode($k_arr, true);
        $logistics_info = array();
        $logistics_default = array();
        if ($k_arr['message'] == 'ok') {
            foreach ($k_arr['data'] as $key => $val) {
                $re = array(
                    'time' => $val['time'],
                    'context' => $val['context'],
                );
                array_push($logistics_info, $re);
            }
        }
        $re1 = array(
            'time' => date('Y-m-d H:i:s'),
            'context' => '您的订单平台拣货完毕，待出库交付' . '' . '快递，' . '运单号为' .'3102939550507',
        );
        array_push($logistics_default, $re1);


        $logistics_info = array_merge($logistics_info, $logistics_default);
       return JsonUtils::successful('成功',$logistics_info);
    }
}