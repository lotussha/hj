<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/9/3
 * Time: 10:49
 */

namespace app\api\controller;


use app\api\logic\goods\GoodsLogic;
use app\common\model\advertisement\BannerModel;
use app\common\model\GoodsCategoryModel;
use app\common\model\settlement\SettlementModel;
use app\Request;
use sakuno\utils\JsonUtils;
use think\App;

class Index extends Api
{
    protected $needAuth = false;
    protected $settlementModel;
    protected $goodsLogic;
    protected $bannerModel;
    public function __construct(Request $request, App $app)
    {
        $this->settlementModel = new SettlementModel();
        $this->goodsLogic = new GoodsLogic();
        $this->bannerModel = new BannerModel();
        parent::__construct($request, $app);
    }

    public function index()
    {
        //轮播列表
        $b_field = 'id,name,type,link_id,img_url,background,sort,skip_type';
        $b_where = [['status','=',1],['start_time','<',time()],['end_time','>',time()],['is_delete','=',0],['position_id','=',1]];
        $b_order = 'sort desc';
        $banner_lists = $this->bannerModel->field($b_field)->where($b_where)->order($b_order)->limit(5)->select()->toArray();

        //弹窗
        $popup_lists = [];

        //热门分类
        $cat_field = 'id,name,image';
        $cat_where = ['parent_id'=>0,'level'=>1,'is_hot'=>1,'is_show'=>1,'is_del'=>0];
        $cat_order = 'sort asc ,id desc';
        $rec_cat_lists = (new GoodsCategoryModel())->getCatList($cat_field,$cat_where,$cat_order,1,5);

        //推荐活动列表
        $activity_lists = [];

        //广播列表
        $radio_lists = [];

        //平台推荐商品
        $rec_field = 'goods_id,original_img,goods_name,market_price,shop_price,is_recommend,prom_type';
        $rec_where = ['page'=>1,'limitpage'=>4,'field'=>$rec_field,'where'=>[['identity','=',1],['is_recommend','=',1],['prom_type','=',0]]];
        $rec_goods_lists = $this->goodsLogic->getGoodsList($rec_where);
        //秒杀专区
        $s_where = ['limit'=>3,'is_recommend'=>1,'page'=>1,'limitpage'=>3];
        $seckill_goods_lists = $this->goodsLogic->getSeckillLists($s_where);

        //拼团专区
        $group_goods_lists = [];

        //推荐门店商品
        $g_field = 'goods_id,identity_id,cat_id,original_img,goods_name,brand_id,market_price,shop_price,store_count,is_recommend,sort';
        $field = 'id,admin_id,identity,nickname,tel,score,province,city,county,twon,address';
        $g_where = ['is_check'=>1];
        $shop_goods_lists = $this->settlementModel
            ->field($field)
            ->with(['goods' => function($query) use ($g_field ,$g_where){
                $query->field($g_field)->where($g_where)->order('sort desc goods_score desc')->withLimit(3);
            }])
            ->where([['examine_is','=',1],['identity','<>',1]])
            ->append(['full_address'])
            ->limit(3)
            ->hidden(['province','city','county','twon','address'])
            ->order('sort desc,score desc')
            ->select()->toArray();

        //门店排行
        $field = 'id,identity,nickname,tel,logo_img,score,province,city,county,twon,address';
        $where = [['identity','<>',1]];
        $shop_ranking_lists = $this->settlementModel->getIdentityLists(0,5,$where,$field);

        $data = [
            'banner_lists'=>$banner_lists,
            'ad_one'=>$banner_lists,
            'ad_two'=>$banner_lists,
            'popup_lists'=>$popup_lists,
            'rec_cat_lists'=>$rec_cat_lists,
            'activity_lists'=>$activity_lists,
            'radio_lists'=>$radio_lists,
            'rec_goods_lists'=>$rec_goods_lists['data'],
            'seckill_goods_lists'=>$seckill_goods_lists,
            'group_goods_lists'=>$group_goods_lists,
            'shop_goods_lists'=>$shop_goods_lists,
            'shop_ranking_lists'=>$shop_ranking_lists,
        ];
        return JsonUtils::successful('获取成功',$data);
    }
}