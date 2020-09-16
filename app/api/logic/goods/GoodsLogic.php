<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/20
 * Time: 12:02
 */

namespace app\api\logic\goods;

use app\api\logic\activity\SeckillLogic;
use app\apiadmin\model\AdminUsers;
use app\common\logic\goods\GoodsPromFactory;
use app\common\model\ActivityModel;
use app\common\model\FreightTemplateModel;
use app\common\model\GoodsAttrModel;
use app\common\model\GoodsImagesModel;
use app\common\model\GoodsModel;
use app\common\model\GoodsSpecPriceModel;
use app\common\model\settlement\SettlementModel;
use app\common\model\user\UserGradeModel;
use app\common\model\user\UserModel;
use sakuno\utils\JsonUtils;
use think\facade\Db;

class GoodsLogic
{
    protected $goodsModel;
    protected $settlementModel;
    protected $seckilllogic;
    public function __construct()
    {
        $this->goodsModel = new GoodsModel();
        $this->settlementModel = new SettlementModel();
        $this->seckilllogic = new SeckillLogic();
    }

    /**
     * 获取商品列表
     * User: Jomlz
     */
    public function getGoodsList($param=[])
    {
        $page = isset($param['page']) && !empty($param['page']) ? $param['page'] : 1;
        $limitpage = isset($param['limitpage']) && !empty($param['limitpage']) ? $param['limitpage'] : 10;
        $where = $param['where'] ?? '';
        $field = $param['field'] ?? '';
        $order = $param['order'] ?? 'sort desc,goods_id desc';
        if (isset($param['identity_id'])){
            $info = $this->settlementModel->findInfo(['id'=>$param['identity_id'],'examine_is'=>1],'id,nickname,examine_is');
            if (!$info){
                return JsonUtils::fail('错误参数');
            }
        }
        $lists = $this->goodsModel
            ->with(['promInfo'=>function($query){
                $query->field('id,type,title,start_time,end_time,status,goods_price,buy_num')->where(['status'=>1,'is_end'=>0]);
            }])
            ->field($field)
            ->where([['is_on_sale','=',1],['is_check','=',1]])
            ->where($where)
            ->scope('where', $param)
            ->append(['prom_type_text'])
//            ->cache(true)
//            ->page($page, $limitpage)
            ->paginate($limitpage)
            ->order($order)
            ->select()->toArray();
//        dump($this->goodsModel->getLastSql());die;
       return $lists;
    }

    /**
     * 获取时间段的秒杀商品列表
     * User: Jomlz
     */
    public function getSeckillLists($param=[])
    {
        $time_space = array_values(flash_sale_time_space());
        $where['field'] = 'id,type,title,start_time,status,sort';
        $where['start_time'] = $start_time = isset($param['start_time']) && !empty($param['start_time']) ?  $param['start_time'] : $time_space[0]['start_time'];
        //获取活动列表
        $seckill = $this->seckilllogic->getAll($where);
        $prom_id = '';
        if ($seckill){
            foreach ($seckill as $k=>$v){
                $prom_id .= ','.$v['id'];
            }
            $prom_id = trim($prom_id,',');
        }
        $param['field'] = 'goods_id,original_img,goods_name,market_price,shop_price,store_count,is_recommend,sort,prom_type,prom_id';
        $param['where'] = [['prom_type','=',2],['prom_id','in',$prom_id]];
        $res = $this->getGoodsList($param);
        if (!empty($res)){
            foreach ($res as $k=>$v){
                $activity = Db::name('activity')->field('100*(FORMAT(buy_num/goods_num,2)) as percent')->where(['id'=>$v['prom_id']])->find();
                $spec_price = Db::name('goods_spec_price')->where(['goods_id'=>$v['goods_id'],'prom_id'=>$v['prom_id'],'prom_type'=>$v['prom_type']])->min('seckill_price');
                $res[$k]['shop_price'] = $spec_price > 0 ? $spec_price : $v['shop_price'];
                $res[$k]['percent'] = $activity['percent'] ?? 0;
                if ($start_time == $time_space[0]['start_time']){
                    $res[$k]['is_start'] = 1;
                }else{
                    $res[$k]['is_start'] = 0;
                }
            }
        }
        return $res;
    }

    /**
     * 获取商品详情
     * User: Jomlz
     */
    public function getGoodsDetails($param=[],$user_id = 0)
    {
        $field = 'goods_id,goods_sn,goods_name,market_price,shop_price,store_count,goods_content,
        sales_sum,prom_type,prom_id,identity,identity_id,is_same_city,is_self_raising,goods_score,is_free_shipping,template_id,service_ids,wholesale_num,wholesale_price,is_member_goods';
        $goods = $this->goodsModel
            ->field($field)
            ->append(['service_arr'])
            ->where(['goods_id'=>$param['goods_id'],'is_check'=>1,'is_on_sale'=>1])
            ->hidden(['service_ids','template_id'])
            ->find();
        if (empty($goods)){
            return ['status'=>0,'msg'=>'商品不存在'];
        }
        //如果参加会员折扣价，登录状态看用户等级折扣，没登录就最高级折扣
        $goods['discount_price'] = $goods['shop_price'];
        if ($goods['is_member_goods'] == 1){
            if ($user_id > 0){
                $user_info = (new UserModel())->with(['UserGrade'=>function($query){$query->field('id,name,discount');}])->field('id,grade_id')->where(['id'=>$user_id])->find()->toArray();
                $goods['discount_price'] = round($goods['shop_price'] * $user_info['UserGrade']['discount'] / 100,2);
            }else{
                $grade = (new UserGradeModel())->where(['status'=>1])->min('discount');
                $goods['discount_price'] = round($goods['shop_price'] * $grade / 100,2);
            }
        }
        //运费
        $freightTemplateModel = new FreightTemplateModel();
        $freight = $freightTemplateModel
            ->with(['freightConfig'=>function($query){
                $query->where(['is_default'=>1]);
            }])
            ->where(['template_id'=>$goods['template_id']])->find();
        $goods['freight_price'] = $freight['freightConfig'][0]['first_money'] ?? '包邮';
        //商品多图
        $goodImg = new GoodsImagesModel();
        $img_lists = $goodImg->field('image_url')->where(['goods_id'=>$goods['goods_id']])->select()->toArray();
        //门店信息
        $adminUserInfo = new AdminUsers();
        $identityInfo = $adminUserInfo->field('id,nickname,avatar,identity,s_id')
            ->with(['identityInfo'=>function($query){
                $query->field('id,identity,nickname,tel')->where(['examine_is'=>1,'is_delete'=>0]);
            }])
            ->where(['status'=>1,'identity'=>$goods['identity'],'id'=>$goods['identity_id']])
            ->find();
        if (empty($identityInfo) || empty($identityInfo['identityInfo'])){
            return ['status'=>0,'msg'=>'门店信息错误'];
        }
        $goods = $goods->toArray();
        //活动商品
        $promInfo = (new ActivityModel())
            ->field('id,type,start_time,end_time,goods_num,buy_num')
            ->where(['id'=>$goods['prom_id'],'status'=>1,'is_del'=>0])
            ->append(['start_time_data','end_time_data','prom_tip'])
            ->hidden(['start_time','end_time','buy_num','goods_num'])
            ->find();
        //商品属性
        $attr_list = (new GoodsAttrModel())->with('attribute')
            ->field('attr_id,attr_value')
            ->where(['goods_id'=>$goods['goods_id'],'is_del'=>0])
            ->select()->toArray();
        $attr = [];
        foreach ($attr_list as $k=>$v){
            $attr[$k]['attr_name'] = $v['attribute']['attr_name'];
            $attr[$k]['attr_value'] = $v['attr_value'];
        }
        //默认规格选中,最低价
        $specGoodsPrice = (new GoodsSpecPriceModel)
            ->where(['goods_id' => $param['goods_id'],'prom_type'=>$goods['prom_type'],'prom_id'=>$goods['prom_id'],'is_del' => 0, 'is_end' => 0])
            ->order('prom_id desc,price asc')
            ->find();
        $spec_data = ['goods_id'=>$goods['goods_id'],'key'=>$specGoodsPrice['key']];
        $default_spec = $this->getSpecPrice($spec_data);
        //规格列表
        $spec_list = $this->get_spec($goods['goods_id'],$goods['prom_id']);
        $data['goods_info'] = $goods;
        $data['goods_banner'] = $img_lists;
        $data['prom_info'] = $promInfo ?? (object)[];
        $data['default_spec'] = $default_spec['data']['goods'] ?? (object)[];
        $data['attr_list'] = $attr;
        $data['spec_list'] = $spec_list;
        $data['shop_info'] = [
            'id'=> $identityInfo['identityInfo']['id'],
            'nickname'=> $identityInfo['identityInfo']['nickname'],
            'avatar'=> $identityInfo['avatar'],
            'identity'=> $identityInfo['identityInfo']['identity'],
            'tel'=> $identityInfo['identityInfo']['tel'],
        ];
        return ['status'=>1,'msg'=>'获取成功','data'=>$data];
    }

    /**
     * 获取商品规格
     * $goods_id 商品id
     * $prom_id 活动id
     * User: Jomlz
     */
    public function get_spec($goods_id,$prom_id=0)
    {
        //商品规格 价钱 库存表 找出 所有 规格项id
        $keys = Db::name('goods_spec_price')
            ->field("GROUP_CONCAT(`key` ORDER BY store_count desc SEPARATOR '_') as kk")
            ->where(['goods_id'=>$goods_id,'is_del'=>0,'is_end'=>0,'prom_id'=>$prom_id])
            ->select()->toArray();
        $keys = $keys[0]['kk'];
        $filter_spec = array();
        $apec_arr = array();
        if ($keys) {
//            $specImage = Db::name('goods_spec_image')->where(['goods_id'=>$goods_id,'src'=>['<>','']])->column("spec_image_id,src");// 规格对应的 图片表， 例如颜色
            $keys = str_replace('_', ',', $keys);
            $sql = "SELECT a.name,a.order,b.* FROM rh_goods_spec AS a INNER JOIN rh_goods_spec_item AS b ON a.id = b.spec_id WHERE b.id IN($keys) ORDER BY b.id";
            $filter_spec2 = Db::query($sql);
            foreach ($filter_spec2 as $key => $val) {
                $filter_spec[$val['name']][] = array(
                    'item_id' => $val['id'],
                    'item' => $val['item'],
//                    'src' => $specImage[$val['id']],
                );
            }
            $apec_arr = [];
            foreach ($filter_spec as $k=>$v){
                $apec['spec_name'] = $k;
                $apec['spec_value'] = $v;
                array_push($apec_arr,$apec);
            }
        }
        return $apec_arr;
    }

    /**
     * 获取规格参数
     * User: Jomlz
     */
    public function getSpecPrice($param=[])
    {
        $spec = new GoodsSpecPriceModel();
        $field = 'goods_id,prom_type,prom_id,identity,identity_id,warehouse_id,shop_price';
        $goods = $this->goodsModel->field($field)->where(['goods_id'=>$param['goods_id'],'is_check'=>1,'is_on_sale'=>1])->find();
        if (empty($goods)){
            return ['status'=>0,'msg'=>'商品信息不存在'];
        }
        $goods = $goods->toArray();
        $specGoodsPrice = $spec
            ->where(['goods_id' => $param['goods_id'], 'key' => $param['key'], 'is_del' => 0, 'is_end' => 0])
            ->append(['spec_price'])
            ->hidden(['seckill_price','rush_price','is_end','is_del','sku','cost_price','commission','bar_code','final_payment_time','profits_percent'])
            ->order('prom_id desc') //优先活动
            ->find();
        if (empty($specGoodsPrice)) {
            return ['status' => 0, 'msg' => '规格参数有误'];
        }
        $specGoodsPrice = $specGoodsPrice->toArray();
        $goodsPromFactory = new GoodsPromFactory();
        if ($goodsPromFactory->checkPromType($goods['prom_type'])) {
            if($specGoodsPrice){
                $goodsPromLogic = $goodsPromFactory->makeModule($goods,$specGoodsPrice);
            }else{
                $goodsPromLogic = $goodsPromFactory->makeModule($goods,null);
            }
            //检查活动是否有效
            if($goodsPromLogic && $goodsPromLogic->checkActivityIsAble()){
                $specGoodsPrice = $goodsPromLogic->getActivityGoodsInfo();
                $specGoodsPrice['activity_is_on'] = 1;
//                unset($specGoodsPrice['spec_price']);
                return ['status'=>1,'msg'=>'该商品参与活动','data'=>['goods'=>$specGoodsPrice]];
            }else{
                $specGoodsPrice['activity_is_on'] = 0;
//                unset($specGoodsPrice['spec_price']);
                return ['status'=>1,'msg'=>'该商品没有参与活动','data'=>['goods'=>$specGoodsPrice]];
            }
        }
//        unset($specGoodsPrice['spec_price']);
        return ['status'=>1,'msg'=>'该商品没有参与活动','data'=>['goods'=>$specGoodsPrice]];
    }

    public function get_goods_cate(&$goodsCate)
    {
        if (empty($goodsCate)) return array();
        $cateAll = $this->get_goods_category_tree();
        if ($goodsCate['level'] == 1) {
            $cateArr = $cateAll[$goodsCate['id']]['tmenu'];
            $goodsCate['parent_name'] = $goodsCate['name'];
            $goodsCate['select_id'] = 0;
        } elseif ($goodsCate['level'] == 2) {
            $cateArr = $cateAll[$goodsCate['parent_id']]['tmenu'];
            $goodsCate['parent_name'] = $cateAll[$goodsCate['parent_id']]['name'];//顶级分类名称
            $goodsCate['open_id'] = $goodsCate['id'];//默认展开分类
            $goodsCate['select_id'] = 0;
        } else {
            $parent = Db::name('goods_category')->where("id", $goodsCate['parent_id'])->order('`sort` desc')->find();//父类
            $cateArr = $cateAll[$parent['parent_id']]['tmenu'];
            $goodsCate['parent_name'] = $cateAll[$parent['parent_id']]['name'];//顶级分类名称
            $goodsCate['open_id'] = $parent['id'];
            $goodsCate['select_id'] = $goodsCate['id'];//默认选中分类
        }
        return $cateArr;
    }

    /**
     * 获取商品一二三级分类
     * User: Jomlz
     */
    public function get_goods_category_tree(){
        $tree = $arr = $result = array();
        $cat_list = Db::name('goods_category')->cache(true)->where(['is_show' => 1,'is_del'=>0])->order('sort')->select();//所有分类
        if($cat_list){
            foreach ($cat_list as $val){
                if($val['level'] == 2){
                    $arr[$val['parent_id']][] = $val;
                }
                if($val['level'] == 3){
                    $crr[$val['parent_id']][] = $val;
                }
                if($val['level'] == 1){
                    $tree[] = $val;
                }
            }
            foreach ($arr as $k=>$v){
                foreach ($v as $kk=>$vv){
                    $arr[$k][$kk]['sub_menu'] = empty($crr[$vv['id']]) ? array() : $crr[$vv['id']];
                }
            }
            foreach ($tree as $val){
                $val['tmenu'] = empty($arr[$val['id']]) ? array() : $arr[$val['id']];
                $result[$val['id']] = $val;
            }
        }
        return $result;
    }
}