<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/12
 * Time: 10:43
 */

namespace app\common\logic;

use app\common\model\FreightConfigModel;
use app\common\model\FreightRegionModel;
use app\common\model\FreightTemplateModel;
use think\facade\Db;

class FreightLogic
{
    protected $goods;//商品模型
    protected $regionId;//地址
    protected $goodsNum;//件数
    private $freightTemplate;
    private $freight = 0;


    /**
     * 设置地址id
     * @param $regionId
     */
    public function setRegionId($regionId)
    {
        $this->regionId = $regionId;
    }

    /**
     * 包含一个商品模型
     * @param $goods
     */
    public function setGoodsModel($goods)
    {
        $this->goods = $goods;
        $FreightTemplate = new FreightTemplateModel();
        $this->freightTemplate = $FreightTemplate->where(['template_id' => $this->goods['template_id']])->find();
    }

    /**
     * 设置商品数量
     * @param $goodsNum
     */
    public function setGoodsNum($goodsNum)
    {
        $this->goodsNum = $goodsNum;
    }

    /**
     * 获取运费
     * @return int
     */
    public function getFreight()
    {
        return $this->freight;
    }

    /**
     * 进行一系列运算
     */
    public function doCalculation()
    {
        if ($this->goods['is_free_shipping'] == 1) {
            $this->freight = 0;
        } else {
            $freightRegion = $this->getFreightRegion();
            $freightConfig = $this->getFreightConfig($freightRegion);
            //计算价格
            switch ($this->freightTemplate['type']) {
                case 2:
                    //按重量
                    $total_unit = $this->goods['total_weight'] ? $this->goods['total_weight'] : $this->goods['weight'] * $this->goodsNum;//总重量
                    break;
                case 3:
                    //按体积
                    $total_unit = $this->goods['total_volume'] ? $this->goods['total_volume'] : $this->goods['volume'] * $this->goodsNum;//总体积
                    break;
                default:
                    //按件数
                    $total_unit = $this->goodsNum;
            }
            $this->freight = $this->getFreightPrice($total_unit, $freightConfig);
        }
    }


    /**
     * 获取区域配置
     */
    private function getFreightRegion()
    {
        //先根据$region_id去查找
        $FreightRegion = new FreightRegionModel();
        $freight_region_where = ['template_id' => $this->goods['template_id'], 'region_id' => $this->regionId];
        $freightRegion = $FreightRegion->where($freight_region_where)->find();
        if (!empty($freightRegion)) {
            return $freightRegion;
        } else {
            $parent_region_id = $this->getParentRegionList([$this->regionId]);
            $parent_region = array();
            foreach ($parent_region_id as $k=>$v){
                array_push($parent_region,$v[0]);
            }
//            $parent_region_id = array('28241','28240');
//            $parent_freight_region_where = ['template_id' => $this->goods['template_id'], 'region_id' => ['IN',$parent_region]];
            $parent_freight_region_where = [['template_id','=',$this->goods['template_id']],['region_id','in',$parent_region]];
            $freightRegion = $FreightRegion->where($parent_freight_region_where)->order('region_id asc')->find();
            return $freightRegion;
        }
    }

    /**
     * 寻找Region_id的父级id
     * @param $cid
     * @return array
     */
    function getParentRegionList($cid)
    {
        $pids = [];
//        $parent_id = Db::name('region')->cache(true)->where(array('id' => ['IN', $cid]))->column('parent_id');
        $parent_id = Db::name('region')->cache(true)->whereIn('id',$cid)->column('parent_id');
        if (!empty($parent_id)) {
//            $pids .= $parent_id;
            array_push($pids, $parent_id);
            $npids = $this->getParentRegionList($parent_id);
            if ($npids) {
                //$pids .= ','.$npids;
                $pids = array_merge($pids, $npids);
            }
        }
        return $pids;
    }

    private function getFreightConfig($freightRegion)
    {
        //还找不到就去看下模板是否启用默认配置
        if (empty($freightRegion)) {
            if ($this->freightTemplate['is_enable_default'] == 1) {
                $FreightConfig = new FreightConfigModel();
                $freightConfig = $FreightConfig->where(['template_id' => $this->goods['template_id'], 'is_default' => 1])->find();
                return $freightConfig;
            } else {
                return null;
            }
        } else {
            return $freightRegion['freightConfig']; //FreightRegionModel模型关联中
        }
    }

    /**
     * 根据总量和配置信息获取运费
     * @param $total_unit
     * @param $freight_config
     * @return mixed
     */
    private function getFreightPrice($total_unit, $freight_config)
    {
        $total_unit = floatval($total_unit);
        if ($total_unit > $freight_config['first_unit']) {
            $average = ceil(($total_unit - $freight_config['first_unit']) / $freight_config['continue_unit']);
            $freight_price = $freight_config['first_money'] + $freight_config['continue_money'] * $average;
        } else {
            $freight_price = $freight_config['first_money'];
        }
        return $freight_price;
    }

}