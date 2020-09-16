<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/20
 * Time: 11:36
 */

namespace app\api\controller\goods;

use app\api\controller\Api;
use app\api\logic\activity\SeckillLogic;
use app\api\logic\goods\GoodsLogic;
use app\common\validate\GoodsValidate;
use app\Request;
use sakuno\utils\JsonUtils;
use think\App;
use think\facade\Db;

class Goods extends Api
{
    protected $goodsLogic;
    protected $seckilllogic;
    protected $validate;
    public function __construct(Request $request, App $app ,GoodsLogic $goodsLogic,SeckillLogic $seckillLogic,GoodsValidate $validate)
    {
        $this->goodsLogic = $goodsLogic;
        $this->seckilllogic = $seckillLogic;
        $this->validate = $validate;
        parent::__construct($request, $app);
    }

    /**
     * 获取商品列表
     * @return \think\Response
     * User: Jomlz
     */
    public function get_lists()
    {
        $goodsLogic = new \app\common\logic\goods\GoodsLogic();
        $this->param['field'] = 'goods_id,cat_id,original_img,goods_name,brand_id,market_price,shop_price,store_count,sales_sum,is_recommend,sort,prom_type,prom_id,one_distribution_price as share_price';
        // 筛选 分类 品牌 规格 属性 价格
        $start_price = $this->param['start_price'] ?? ''; //属性加入帅选条件中
        $end_price = $this->param['end_price'] ?? ''; //属性加入帅选条件中
        $price = $this->param['price'] ?? '';  // 价钱
        $spec = $this->param['spec'] ?? '';
        $attr = $this->param['attr'] ?? '';
        $brand_id = $this->param['brand_id'] ?? 0;
        $sel =$this->param['sel'] ?? '';
        $prom_type =$this->param['prom_type'] ?? 0;

        $filter_param = [];
        if($start_price && $end_price) $price = $start_price.'-'.$end_price; // 如果输入框有价钱 则使用输入框的价钱
        $filter_param['cat_id'] = $this->param['cat_id'] ?? 1; //加入帅选条件中
        $brand_id  && ($filter_param['brand_id'] = $brand_id); //加入帅选条件中
        $spec && ($filter_param['spec'] = $spec); //加入帅选条件中
        $attr && ($filter_param['attr'] = $attr); //加入帅选条件中
        $price && ($filter_param['price'] = $price); //加入帅选条件中

        $cat_id = $this->param['cat_id'] ?? '';
        $cat_id_arr = $goodsLogic->getCatGrandson($cat_id);
        $goods_where = [['is_on_sale','=',1],['is_check','=',1],['cat_id','in',$cat_id_arr]];
//        $filter_goods_id = Db::name('goods')->where($goods_where)->cache(true)->column("goods_id");
        $filter_goods_id = Db::name('goods')->where($goods_where)->column("goods_id");
//        dump($filter_goods_id);die;
        // 过滤筛选的结果集里面找商品
        // 品牌或者价格
        if($brand_id || $price)
        {
            $goods_id_1 = $goodsLogic->getGoodsIdByBrandPrice($brand_id,$price); // 根据 品牌 或者 价格范围 查找所有商品id
            $filter_goods_id = array_intersect($filter_goods_id,$goods_id_1); // 获取多个帅选条件的结果 的交集
        }
        // 规格
        if($spec)
        {
            $goods_id_2 = $goodsLogic->getGoodsIdBySpec($spec); // 根据 规格 查找当所有商品id
            $filter_goods_id = array_intersect($filter_goods_id,$goods_id_2); // 获取多个帅选条件的结果 的交集
        }

        if($attr)// 属性
        {
            $goods_id_3 = $goodsLogic->getGoodsIdByAttr($attr); // 根据 规格 查找当所有商品id
            $filter_goods_id = array_intersect($filter_goods_id,$goods_id_3); // 获取多个帅选条件的结果 的交集
        }

        //筛选网站自营,入驻商家,货到付款,仅看有货,促销商品
        if($sel)
        {
            $goods_id_4 = $goodsLogic->getFilterSelected($sel,$cat_id_arr);
            $filter_goods_id = array_intersect($filter_goods_id,$goods_id_4);
        }
        //活动
        if ($prom_type > 0)
        {
            $goods_id_5 = $goodsLogic->getPromGoods($prom_type);
            $filter_goods_id = array_intersect($filter_goods_id,$goods_id_5);
        }
        //根据分类下的可筛选条件
        $filter_menu  = $goodsLogic->get_filter_menu($filter_param,'goodsList'); // 获取显示的帅选菜单
        $filter_price = $goodsLogic->get_filter_price($filter_goods_id,$filter_param,'goodsList'); //筛选的价格期间
        $filter_brand = $goodsLogic->get_filter_brand($filter_goods_id,$filter_param); // 获取指定分类下的帅选品牌
        $filter_spec  = $goodsLogic->get_filter_spec($filter_goods_id,$filter_param,'goodsList',1); // 获取指定分类下的帅选规格
        $filter_attr  = $goodsLogic->get_filter_attr($filter_goods_id,$filter_param,'goodsList',1); // 获取指定分类下的帅选属性

        //查询条件
        $this->param['where'] = [['goods_id','in',$filter_goods_id],['prom_type','=',$prom_type]];
//        dump($this->param);die;
        $lists = $this->goodsLogic->getGoodsList($this->param);
        $screen_lists = [
            'filter_price'=>$filter_price,
            'filter_brand'=>$filter_brand,
            'filter_spec'=>$filter_spec,
            'filter_attr'=>$filter_attr,
        ];
        $data = ['screen_lists'=>$screen_lists,$lists];
        return JsonUtils::successful('获取成功',$data);
    }

    /**
     * 秒杀时间组
     */
    public function flash_sale_time_space()
    {
        $time_space = array_values(flash_sale_time_space());
        $data['time_space'] = $time_space;
        return JsonUtils::successful('获取成功',$data);
    }

    /**
     * 秒杀商品列表
     * User: Jomlz
     */
    public function seckill_lists()
    {
        $res = $this->goodsLogic->getSeckillLists($this->param);
        return JsonUtils::successful('获取成功',$res);
    }

    /**
     * 获取商品信息
     * User: Jomlz
     * Date: 2020/8/21 13:56
     */
    public function goods_details()
    {
        $validate_result = $this->validate->scene('info')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($this->validate->getError());
        }
        $res = $this->goodsLogic->getGoodsDetails($this->param,$this->user_id);
        if ($res['status'] == 0){
            return JsonUtils::fail($res['msg']);
        }
        return JsonUtils::successful('获取成功',$res['data']);
    }

    /**
     * 获取规格价格
     * @return \think\Response
     * User: Jomlz
     */
    public function get_spec_price()
    {
        $validate_result = $this->validate->scene('spec')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($this->validate->getError());
        }
        $res = $this->goodsLogic->getSpecPrice($this->param);
        if ($res['status'] == 0){
            return JsonUtils::fail($res['msg']);
        }
        return JsonUtils::successful($res['msg'],$res['data']);
//        return JsonUtils::successful('获取成功',$res['data']['spec_price']);
    }
}