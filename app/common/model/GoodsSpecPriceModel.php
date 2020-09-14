<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/5
 * Time: 16:26
 */

namespace app\common\model;

use think\Model;

class GoodsSpecPriceModel extends Model
{
    protected $name = 'goods_spec_price';

    /**
     * 获取规格信息
     * User: Jomlz
     */
    public function getSpecPriceAttr($value,$data)
    {
        $spec_arr = ['original_price'=>$data['original_price'],'price'=>$data['price'],'store_count'=>$data['store_count'],'prom_type'=>$data['prom_type'],'prom_id'=>$data['prom_id']];
        if ($data['prom_id'] > 0){
            switch ($data['prom_type']){
                case 2: //秒杀
                    $spec_arr = ['original_price'=>$data['original_price'],'price'=>$data['seckill_price'],'store_count'=>$data['store_count'],'prom_type'=>$data['prom_type'],'prom_id'=>$data['prom_id']];
                    break;
                case 3: //预售
                    $spec_arr = ['original_price'=>$data['original_price'],'price'=>$data['pre_sale_price'],'deposit'=>$data['deposit'],'store_count'=>$data['store_count'],'prom_type'=>$data['prom_type'],'prom_id'=>$data['prom_id']];
                   break;
            }
        }

        return $spec_arr;
    }

    /**
     * 关联活动
     * User: Jomlz
     */
    public function specProm()
    {
        return $this->hasOne(ActivityModel::class,'id','prom_id')->append(['start_time_data','end_time_data']);
    }
}