<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/20
 * Time: 17:21
 */

namespace app\api\controller\identity;

use app\api\controller\Api;
use app\common\logic\goods\GoodsLogic;
use app\common\model\settlement\SettlementModel;
use app\Request;
use sakuno\utils\JsonUtils;
use think\App;

class Identity extends Api
{
    protected $settlementModel;
    protected $goodsLogic;
    public function __construct(Request $request, App $app , GoodsLogic $goodsLogic)
    {
        $this->settlementModel = new SettlementModel;
        $this->goodsLogic = $goodsLogic;
        parent::__construct($request, $app);
    }

    /**
     * 列表
     * @return \think\Response
     * User: Jomlz
     */
    public function lists()
    {
        $field = 'id,identity,nickname,tel,score,province,city,county,twon,address';
        $where = [['examine_is','=',1]];
        $lists = $this->settlementModel->getIdentityLists(0,5,$where,$field);
        $data = ['lists'=>$lists];
        return JsonUtils::successful('获取成功',$data);
    }

    /**
     * 首页推荐门店商品
     * User: Jomlz
     */
    public function goods_list()
    {
        $g_field = 'goods_id,identity_id,cat_id,original_img,goods_name,brand_id,market_price,shop_price,store_count,is_recommend,sort';
        $field = 'id,admin_id,identity,nickname,tel,score,province,city,county,twon,address';
        $g_where = ['is_check'=>1];
        $lists = $this->settlementModel
            ->field($field)
            ->with(['goods' => function($query) use ($g_field ,$g_where){
                $query->field($g_field)->where($g_where)->order('sort desc goods_score desc')->withLimit(3);
            }])
            ->where([['examine_is','=',1],['identity','<>',1],['admin_id','<>',1]])
            ->append(['full_address'])
            ->limit(3)
            ->hidden(['province','city','county','twon','address'])
            ->order('sort desc,score desc')
            ->select()->toArray();
        $data = ['lists'=>$lists];
        return JsonUtils::successful('获取成功',$data);
    }
}