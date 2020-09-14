<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/4
 * Time: 14:38
 */

namespace app\common\model;

use think\Model;

class GoodsSpecModel extends Model
{
    protected $name = 'goods_spec';

    public function goodsType()
    {
        return $this->hasOne('GoodsTypeModel','id','type_id');
    }

    public function goodsSpecItem()
    {
        return $this->hasMany('GoodsSpecItemModel','spec_id','id');
    }

    /**
     * 获取模型的规格列表
     * @param $type_id 模型id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * User: Jomlz
     * Date: 2020/8/4 14:59
     */
    public function getTypeSpecList($type_id)
    {
        $list = $this->with(['goodsType','goodsSpecItem'])->where(['type_id'=>$type_id,'is_del'=>0])->select()->toArray();
        return $list;
    }

    public function getSpecItemInfo($id)
    {
        return $this->with(['goodsType','goodsSpecItem'])->where("id=".$id)->select()->toArray();
    }

    //关联规格项
    public function items()
    {
        return $this->hasMany('GoodsSpecItemModel','spec_id','id');
    }


    public function getGoodsSpecItems($param=[])
    {
        $type_id = $param['type_id'] ?? '';
        $goods_id = $param['goods_id'] ?? '';
        $goods_spec_data=[];
        $goods_spec=[];
        $goods = new GoodsModel();
        $goodsSpecPrice = new GoodsSpecPriceModel();
        $goodsAttr = new GoodsAttrModel();
        $goods_info = $goods->scope('where',$param)->where(['goods_id'=>$goods_id])->find();
        if ($goods_info && $goods_info['goods_type'] != 0)
        {
            $type_id = $goods_info['goods_type'];
            $goods_spec = $goodsSpecPrice
                ->field('item_id,key,price,original_price,cost_price,market_price,wholesale_num,wholesale_price,store_count')
                ->where(['goods_id'=>$goods_id,'is_del'=>0,'prom_type'=>0])->select()->toArray();
            foreach ($goods_spec as $k=>$v){
                $temp_array=explode('_',$v['key']);

                foreach ($temp_array as $key=>$value){
                    $goods_spec_data[]=$value;
                }
            }
        }
        $specList = self::where(['type_id'=>$type_id])->with('items')->order('order desc')->select()->toArray();
        foreach ($specList as $key=>$val){
            foreach ($val['items'] as $k=>$v){
                if(in_array($v['id'],$goods_spec_data)){
                    $val['items'][$k]['check']=1;
                }else{
                    $val['items'][$k]['check']=0;
                }
            }
            $specList[$key]['items'] = $val['items'];
        }
        $attribute = new GoodsAttributeModel();
        $attrList = $attribute->where(['type_id'=>$type_id])->select()->toArray();

        $goodsAttr = $goodsAttr->where(['goods_id'=>$goods_id])->select();
        foreach ($attrList as $key=>$val){
            foreach ($goodsAttr as $k=>$v){
                if($v['attr_id']==$val['attr_id']){
                    $attrList[$key]['attr_values']=$v['attr_value'];
                    if($val['attr_input_type']==1){
                        $attrList[$key]['check_value']=$v['attr_value'];
                    }else{
                        $attrList[$key]['check_value']="";
                    }
                }
            }
            if ($val['attr_input_type'] == 1){
                $option_val = explode(PHP_EOL,$val['attr_values']);
                $attrList[$key]['attr_values'] =  $option_val;
            }
        }
        return ['goods_spec'=>$goods_spec,'spec_list'=>$specList,'attr_list'=>$attrList];
    }
}