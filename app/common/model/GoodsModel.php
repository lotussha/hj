<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/4
 * Time: 18:14
 */

namespace app\common\model;

use app\common\logic\HandleLogic;
use app\common\model\settlement\SettlementModel;
use think\facade\Db;
use think\Model;

class GoodsModel extends CommonModel
{
    protected $name = 'goods';

    //可搜索字段
    protected $searchField = [
        'goods_name',
        'goods_sn',
    ];
    //可作为条件的字段
    protected $whereField = [
//        'cat_id',
        'identity',
        'identity_id',
        'warehouse_id',
        'is_del',
        'is_recommend',
    ];

    //角色后台名称
    public function identityInfo()
    {
        return $this->hasOne(SettlementModel::class, 'admin_id', 'identity_id')->field('admin_id,nickname,examine_is');
    }

    //商品分类
    public function goodsCategory()
    {
        return $this->hasOne('GoodsCategoryModel', 'id', 'cat_id')->field('id,name');
    }

    //商品仓库
    public function goodsWarehouse()
    {
        return $this->hasOne(SettlementModel::class, 'admin_id', 'warehouse_id')->field('admin_id,nickname,examine_is');
    }

    //商品多图
    public function goodsImgs()
    {
        return $this->hasMany(GoodsImagesModel::class,'goods_id','goods_id');
    }

    //商品服务
    public function getServiceArrAttr($value,$data)
    {
        $service_ids = explode(',',$data['service_ids']);
        $service = [];
        foreach ($service_ids as $k=>$v){
            $serviceInfo = (new GoodsServiceModel)->where(['id'=>$v,'is_del'=>0])->hidden(['add_time','is_del'])->find();
            if ($serviceInfo){
                array_push($service,$serviceInfo);
            }
        }
        return $service;
    }

    //上下架
    public function getIsOnSaleTextAttr($value, $data)
    {
        $text = [
            0 => '下架中',
            1 => '上架中'
        ];
        return $text[$data['is_on_sale']];
    }

    //身份类型
    public function getIdentityTypeAttr($value,$data)
    {
        return config('status')['IDENTITY'][$data['identity']];
    }

    //活动类型
    public function getPromTypeTextAttr($value,$data)
    {
        return config('status')['PROM_TYPE'][$data['prom_type']];
    }
    //活动信息
    public function promInfo()
    {
        return $this->hasOne(ActivityModel::class,'id','prom_id');
    }

    //关联品牌
    public function brand()
    {
        return $this->hasOne(GoodsBrandModel::class,'id','brand_id')->field('id,name');
    }

}