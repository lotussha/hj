<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/21
 * Time: 16:47
 */

namespace app\api\logic\cart;


use app\api\logic\goods\GoodsLogic;
use app\api\logic\order\OrderLogic;
use app\common\logic\activity\SeckillLogic;
use app\common\logic\goods\GoodsPromFactory;
use app\common\model\ActivityModel;
use app\common\model\cart\GoodsCartModel;
use app\common\model\GoodsSpecPriceModel;
use sakuno\utils\JsonUtils;
use think\Exception;

class CartLogic
{
    protected $cartModel;
    protected $goodsLogic;
    public function __construct(GoodsCartModel $cartModel,GoodsLogic $goodsLogic)
    {
        $this->cartModel = $cartModel;
        $this->goodsLogic = $goodsLogic;
    }

    public function getCartList($param=[])
    {
        $page = isset($param['page']) && !empty($param['page']) ? $param['page'] : 1;
        $limitpage = isset($param['limitpage']) && !empty($param['limitpage']) ? $param['limitpage'] : 10;
        $where = $param['where'] ?? [];
        $CartList = $this->cartModel
            ->with(['goods','goods.identityInfo'])
            ->where(['user_id'=>$param['user_id'],'is_del'=>0])
            ->hidden(['bar_code','is_del','add_time'])
//            ->where($where)
//            ->page($page,$limitpage)
            ->select()
            ->toArray();
        //检测购物车信息
        $cartCheckAfterList = array_values($this->checkCartList($CartList));
        //根据identity_id分组
        $identity_array = []; //初始化一个数组
        foreach ($cartCheckAfterList as $key=>$value){
            $identity_array[$value['goods']['identity_id']][] = $value;
        }
        $identity_array = array_values($identity_array);
        //身份信息分组,友好输出格式
        $arr = [];
        foreach ($identity_array as $k=>$v){
            $identity['identity_info'] = $v[0]['goods']['identityInfo'];
            foreach ($v as $kk=>$vv){
                unset($v[$kk]['goods']['identityInfo']);
            }
            $identity['identity_goods'] = $v;
            array_push($arr,$identity);
        }
        return $arr;
    }

    /*
     * 添加购物车
     * User: Jomlz
     */
    public function addCart($param=[])
    {
        //检查商品信息
        $goods = $this->goodsLogic->getGoodsDetails($param);
        if ($goods['status'] == 0){
            return JsonUtils::fail($goods['msg']);
        }
        $goods_info = $goods['data']['goods_info'];
        //查看该商品是否有规格数据
        $spec = $this->goodsLogic->getSpecPrice($param);
        if ($spec['status'] == 0){
            return JsonUtils::fail($spec['msg']);
        }
        $spec_info = $spec['data']['goods'];
        //检测活动商品加入购物车的要求？
        if (!$this->cartModel->checkAddCart($goods_info['prom_type']))
        {
            return JsonUtils::fail('该活动商品不能加入购物车');
        }
        if ($spec_info)
        //获取用户购物车的商品有多少种？不能超过20种
        $userCartCount = $this->cartModel->where(['user_id' => $param['user_id'],'is_del'=>0])->count();
        if ($userCartCount >= 20) {
            return JsonUtils::fail('购物车最多只能放20种商品');
        }
        //查询购物车是否已有该规格商品
        $is_carGoods = $this->cartModel->where(['user_id'=>$param['user_id'],'goods_id'=>$param['goods_id'],'spec_key'=>$param['key'],'is_del'=>0])->find();
        if ($is_carGoods){
            $res = $this->cartModel->where(['id'=>$is_carGoods['id']])->inc('goods_num',$param['goods_num'])->update();
        }else{
            $data = array(
                'user_id'=>$param['user_id'],
                'goods_id'=>$param['goods_id'],
                'goods_name'=>$goods_info['goods_name'],
                'goods_num'=>$param['goods_num'],
                'item_id'=>$spec_info['item_id'],
                'spec_key'=>$param['key'],
                'spec_key_name'=>$spec_info['key_name'],
                'goods_sn'=>$goods_info['goods_sn'],
                'market_price'=>$spec_info['market_price'],
                'goods_price'=>$spec_info['price'],
                'prom_type'=>$spec_info['prom_type'],
                'prom_id'=>$spec_info['prom_id'],
                'add_time'=>time()
            );
            $res = $this->cartModel->insert($data);
        }
        if ($res){
            return JsonUtils::successful('加入购物车成功');
        }else{
            return JsonUtils::fail('加入购物车失败');
        }
    }

    /**
     * 过滤掉无效的购物车商品
     * 1下架/移除 2 活动结束
     */
    public function checkCartList($cartList){
        $goodsPromFactory = new GoodsPromFactory();
        foreach ($cartList as $key=>$value){
            $sta_arr = ['status'=>0,'msg'=>'正常'];
            //商品不存在或者已经下架
            if (empty($value['goods']) || $value['goods']['is_on_sale'] != 1 || $value['goods']['is_del'] == 1 || $value['goods']['is_check'] !=1){
                $sta_arr = ['status'=>1,'msg'=>'商品已下架'];
            }
            //获取商品规格信息
            $specGoodsPrice = (new GoodsSpecPriceModel())
                ->with(['specProm'=>function($query){
                    $query->where(['status'=>1,'is_del'=>0])
                        ->hidden(['status','is_end','add_time','start_time','end_time','is_del','sort','identity','identity_id']);
                }])
                ->where(['goods_id' => $value['goods_id'],'item_id'=>$value['item_id']])
                ->hidden(['bar_code','sku','spec_img','add_time','is_del'])
                ->find();
            if (!$specGoodsPrice || $specGoodsPrice['is_del'] == 1 || $specGoodsPrice['is_end']){
                $sta_arr = ['status'=>2,'msg'=>'商品规格已下架'];
            }
            if ($specGoodsPrice['store_count'] == 0){
                $sta_arr = ['status'=>3,'msg'=>'已售罄'];
            }
            $specGoodsPrice = $specGoodsPrice->toArray();
            //商品的活动是否失效
            if ($goodsPromFactory->checkPromType($value['prom_type'])) {
                $goodsPromLogic = $goodsPromFactory->makeModule($value['goods'], $specGoodsPrice);
                if ($goodsPromLogic && !$goodsPromLogic->isAble()) {
                    unset($specGoodsPrice['specProm']);
                    $sta_arr = ['status'=>4,'msg'=>'已失效'];
                }
                if ($goodsPromLogic && !$goodsPromLogic->checkActivityIsAble()){
                    $sta_arr = ['status'=>5,'msg'=>'未开始'];
                }else{
                    $sta_arr = ['status'=>6,'msg'=>'进行中'];
                }
            }
            $sta_arr = ['status'=>$sta_arr['status'],'msg'=>$sta_arr['msg'],'prom_type'=>$goodsPromFactory->promTypeText($value['prom_type'])];
            $cartList[$key]['spec_prom'] = $specGoodsPrice['specProm'] ?? [];
            $cartList[$key]['store_count'] = $specGoodsPrice['store_count'];
            $cartList[$key]['sta_arr'] = $sta_arr;
        }
        return array_values($cartList);
    }

    /**
     * 编辑购物车
     * act：change_num修改数量 del删除
     * User: Jomlz
     */
    public function cartHandle($param=[])
    {
        $car_info = $this->cartModel
            ->with(['goods','goods.identityInfo'])
            ->where(['id'=>$param['id'],'user_id'=>$param['user_id'],'is_del'=>0])
            ->find();
        if (empty($car_info)){
            return JsonUtils::fail('信息错误');
        }
        $checkCartList = [];
        array_push($checkCartList,$car_info->toArray());
        //检测购物车信息
        $cartCheckAfterList = array_values($this->checkCartList($checkCartList))[0];
//        if ($cartCheckAfterList['sta_arr']['status'] != 0){
//            return JsonUtils::fail($cartCheckAfterList['sta_arr']['msg']);
//        }
        //修改购物车商品数量
        if ($param['act'] == 'change_num'){
            if ($param['goods_num'] > $cartCheckAfterList['store_count']){
                return JsonUtils::fail('超出库存');
            }else{
                $res = $this->cartModel->where(['id'=>$param['id']])->save(['goods_num'=>$param['goods_num']]);
            }
        }
        if ($param['act'] == 'change_selected'){
            $res = $this->cartModel->where(['id'=>$param['id']])->save(['selected'=>$param['selected']]);
        }
        if ($param['act'] == 'del'){
            $res = $this->cartModel->where(['id'=>$param['id']])->save(['is_del'=>1]);
        }
        if ($res){
            return JsonUtils::successful('成功');
        }else{
            return JsonUtils::fail('失败');
        }
    }
}