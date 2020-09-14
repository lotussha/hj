<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/10
 * Time: 11:00
 */

namespace app\apiadmin\logic;

use app\common\logic\HandleLogic;
use app\common\model\GoodsBrandModel;
use app\common\model\GoodsModel;
use app\common\validate\GoodsBrandValidate;
use sakuno\utils\JsonUtils;
use think\facade\Db;

class GoodsLogic
{
    /**
     * 获取商品列表
     * @param int $page 页
     * @param int $list_rows 页数
     * @param $param 传参
     * User: Jomlz
     * Date: 2020/8/11 14:07
     */
    public function getGoodsList($page = 1, $list_rows = 10,$param=[])
    {
        $newwhere['is_del'] = $param['is_del'] ?? 0;
        $cat_field = $param['cat_field'] ?? '';
        $cat_where = $param['cat_field'] ?? '';
        $field = $param['field'] ?? '';
        $where = $param['where'] ?? '';
        $goodsModel = new GoodsModel();
        $lists = $goodsModel
            ->with(['goodsCategory' => function ($query) use ($cat_field, $cat_where) {
                $query->field($cat_field)->where($cat_where);
            },'goodsWarehouse','brand','identityInfo'])
            ->field($field)
            ->where($where)
            ->where($newwhere)
            ->scope('where', $param)
            ->append(['is_on_sale_text','identity_type'])
            ->hidden(['cat_id','brand_id','warehouse_id','identity','identity_id'])
            ->paginate($list_rows)->toArray();
//        dump($goodsModel->getLastSql());die;
        foreach ($lists['data'] as $k => $v) {
            $lists['data'][$k]['identity_name'] = $v['identityInfo']['nickname'] ?? '';
            $lists['data'][$k]['goods_cate_name'] = $v['goodsCategory']['name'] ?? '';
            $lists['data'][$k]['warehouse_name'] = $v['goodsWarehouse']['nickname'] ?? '';
            $lists['data'][$k]['brand_name'] = $v['brand']['name'] ?? '';
            unset($lists['data'][$k]['goodsCategory'], $lists['data'][$k]['goodsWarehouse'],$lists['data'][$k]['brand'],$lists['data'][$k]['identityInfo']);
        }
        return $lists;
    }

    public function addEditGoodsInfo($param = [], $admin_id = '')
    {
        $res = array_return();
        if (isset($param['on_time'])) {
            $param['on_time'] = time();//上架时间
        }
        //判断身份可传参数
        if (isset($param['identity']) && ($param['identity'] != 1 || $param['identity'] != 2) && isset($param['replace_sell_price']) && $param['replace_sell_price'] > 0){
            $res['status'] = 0;
            $res['msg'] = '非法传参';
            return $res;
        }
        $text = [
            "9_12" => [
                "price" => "99.00",
                "key_name" => "选择版本:全网通3G+32G 选择颜色:铂光色",
                "market_price" => "129.00",
                "original_price" => "100.00",
                "cost_price" => "88.00",
                "wholesale_num" => "3",
                "wholesale_price" => "95",
                "store_count" => "100",
            ],
            "9_11" => [
                "price" => "222.00",
                "key_name" => "选择版本:全网通3G+32G 选择颜色:红色",
                "market_price" => "299.00",
                "original_price" => "210.00",
                "cost_price" => "200.00",
                "wholesale_num" => "5",
                "wholesale_price" => "210",
                "store_count" => "100",
            ],
        ];
//        dump(json_encode($text,JSON_UNESCAPED_UNICODE));die;
//        dump(json_decode($param['goods_common'],true));die;
        $catInfo = Db::name('goods_category')->where(['id'=>$param['cat_id']])->find();
        $param['extend_cat_id'] = $catInfo['parent_id_path'];
        $handleLogic = new HandleLogic();
        $goodsModel = new GoodsModel();
        // 启动事务
        Db::startTrans();
        try {
            if (isset($param['goods_id']) && !empty($param['goods_id'])) {
                if (isset($param['identity']) && isset($param['identity_id'])){
                    $goodsInfo = $goodsModel->where(['goods_id'=>$param['goods_id'],'identity'=>$param['identity'],'identity_id'=>$param['identity_id']])->find();
                    if (empty($goodsInfo)){
                        $res['status'] = 0;
                        $res['msg'] = '商品不存在';
                        return $res;
                    }
                }
                $res = $handleLogic->Handle($param, 'GoodsModel', 'edit', 'goods_id');
                Db::name('goods_attr')->where(['goods_id' => $param['goods_id']])->delete();
//                Db::name('goods_spec_price')->where(['goods_id'=>$param['goods_id']])->delete();
                Db::name('goods_spec_price')->where(['goods_id' => $param['goods_id']])->save(['is_del' => 1]);
                Db::name('goods_images')->where(['goods_id' => $param['goods_id']])->delete();
            } else {
                $param['add_time'] = time();
                $res = $handleLogic->Handle($param, 'GoodsModel', 'add', 'goods_id');
            }
            $goods_id = $res['object_id'];
            $goods_sn = mt_rand(1000, 9999) . date('ymd') . str_pad($goods_id, 7, "0", STR_PAD_LEFT);
            Db::name('goods')->where("goods_id = $goods_id")->save(array("goods_sn" => $goods_sn));
            //商品相册
            if (isset($param['goods_images']) && $goods_id && !empty($param['goods_images'])) {
                $images = explode(',', $param['goods_images']);
                foreach ($images as $k => $v) {
                    Db::name('goods_images')->insert([
                        'goods_id' => $goods_id,
                        'image_url' => $v,
                    ]);
                }
            }
            //商品属性
            if (isset($param['atrr_arr']) && !empty($param['atrr_arr']) && $goods_id) {
                $param['atrr_arr'] = json_decode($param['atrr_arr'], true);
                foreach ($param['atrr_arr'] as $k => $v) {
                    Db::name('goods_attr')->insert([
                        'goods_id' => $goods_id,
                        'attr_id' => $k,
                        'attr_value' => $v,
                    ]);
                }
            }
            //商品规格
            if (isset($param['goods_common']) && !empty($param['goods_common']) && $goods_id) {
                $param['goods_common'] = json_decode($param['goods_common'], true);
                foreach ($param['goods_common'] as $key => $val) {
                    Db::name('goods_spec_price')->insert([
                        'goods_id' => $goods_id,
                        'key' => $key,
                        'key_name' => $val['key_name'],
                        'price' => $val['price'],
                        'original_price' => $val['original_price'],
                        'market_price' => $val['market_price'],
                        'cost_price' => $val['cost_price'],
                        'wholesale_num' => $val['wholesale_num'],
                        'wholesale_price' => $val['wholesale_price'],
                        'store_count' => $val['store_count'],
//                        'sku' => $val['sku'],
                    ]);
                    //记录库存日志
                    update_stock_log($admin_id, $val['store_count'], array('goods_id' => $goods_id, 'goods_name' => $param['goods_name'], 'spec_key_name' => $val['key_name']));
                }
            }
            //记录操作日志

            //修改商品后购物车的商品价格也修改一下

            //刷新库存
            $this->refreshStock($goods_id);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            $res['status'] = 0;
            $res['msg'] = "提交商品失败," . $e->getMessage();
            Db::rollback();
        }
        return $res;
    }

    public function refreshStock($goods_id)
    {
        $count = Db::name("goods_spec_price")->where("goods_id", $goods_id)->count();
        if ($count == 0) return false; // 没有使用规格方式 没必要更改总库存

        $store_count = Db::name("goods_spec_price")->where("goods_id", $goods_id)->sum('store_count');
        Db::name("Goods")->where("goods_id", $goods_id)->save(array('store_count' => $store_count)); // 更新商品的总库存
    }

    /**
     * 改变或者添加分类时 需要修改他下面的 parent_id_path  和 level
     * @param $id 商品id
     * User: Jomlz
     * Date: 2020/8/10 14:10
     */
    public function refreshCat($id)
    {
        $cat = Db::name("goods_category")->where("id = $id")->find(); // 找出他自己
        // 刚新增的分类先把它的值重置一下
        if($cat['parent_id_path'] == '')
        {
            ($cat['parent_id'] == 0) && Db::execute("UPDATE rh_goods_category set  parent_id_path = '0_$id', level = 1 where id = $id"); // 如果是一级分类
            Db::execute("UPDATE rh_goods_category AS a ,rh_goods_category AS b SET a.parent_id_path = CONCAT_WS(',',b.parent_id_path,'$id'),a.level = (b.level+1) WHERE a.parent_id=b.id AND a.id = $id");
            $cat = Db::name("goods_category")->where("id = $id")->find(); // 从新找出他自己
        }
        if($cat['parent_id'] == 0) //有可能是顶级分类 他没有老爸
        {
            $parent_cat['parent_id_path'] =   '0';
            $parent_cat['level'] = 0;
        }
        else{
            $parent_cat = Db::name("goods_category")->where(['id'=>$cat['parent_id']])->find(); // 找出他老爸的parent_id_path
        }
        $replace_level = $cat['level'] - ($parent_cat['level'] + 1); // 看看他 相比原来的等级 升级了多少  ($parent_cat['level'] + 1) 他老爸等级加一 就是他现在要改的等级
        $replace_str = $parent_cat['parent_id_path'].','.$id;
        Db::execute("UPDATE `rh_goods_category` SET parent_id_path = REPLACE(parent_id_path,'{$cat['parent_id_path']}','$replace_str'), level = (level - $replace_level) WHERE  parent_id_path LIKE '{$cat['parent_id_path']}%'");
    }

    public function delGoods($goods_ids)
    {
//        // 判断此商品是否有订单
//        $ordergoods_count = Db::name('order_goods')->whereIn('goods_id',$goods_ids)->group('goods_id')->field('goods_id');
//        if($ordergoods_count)
//        {
//            $goods_count_ids = implode(',',$ordergoods_count);
//            return ['status'=>0,'msg'=>"ID为【{$goods_count_ids}】的商品有订单,不得删除!"];
//        }
//        // 商品团购
//        $groupBuy_goods = Db::name('group_buy')->whereIn('goods_id',$goods_ids)->group('goods_id')->field('goods_id',true);
//        if($groupBuy_goods)
//        {
//            $groupBuy_goods_ids = implode(',',$groupBuy_goods);
//            return ['status'=>0,'msg'=>"ID为【{$groupBuy_goods_ids}】的商品有团购,不得删除!"];
//        }

        Db::name("goods")->whereIn('goods_id',[$goods_ids])->update(['is_del'=>1,'is_on_sale'=>0]);  //商品表
//        Db::name("goods_images")->whereIn('goods_id',[$goods_ids])->update(['is_del'=>1]);  //商品相册
//        Db::name("goods_spec_price")->whereIn('goods_id',[$goods_ids])->update(['is_del'=>1]);  //商品规格
//        Db::name("goods_attr")->whereIn('goods_id',[$goods_ids])->update(['is_del'=>1]);  //商品属性

//        Db::name("cart")->whereIn('goods_id',$goods_ids)->update(['is_del'=>1]);  // 购物车
//        Db::name("comment")->whereIn('goods_id',$goods_ids)->update(['is_del'=>1]);  //商品评论
//        Db::name("goods_consult")->whereIn('goods_id',$goods_ids)->update(['is_del'=>1]);  //商品咨询
//        Db::name("goods_collect")->whereIn('goods_id',$goods_ids)->update(['is_del'=>1]);  //商品收藏
        return JsonUtils::successful('操作成功');
    }

    /**
     * 获取品牌列表
     * User: Jomlz
     * Date: 2020/8/10 19:44
     */
    public function BrandList($param)
    {
        $GoodsBrandModel = new GoodsBrandModel();
        $where = ['is_del'=>0];
        $field = '';
        $lists = $GoodsBrandModel->with(['goodsCategory'])
            ->field($field)
            ->where($where)
            ->scope('where', $param)
            ->paginate($param['list_rows'])->toArray();
        foreach ($lists['data'] as $k => $v) {
            $lists['data'][$k]['goods_cate_name'] = $v['goodsCategory']['name'];
            unset($lists['data'][$k]['goodsCategory']);
        }
        return $lists;
    }

    public function brandHandle($act,$param)
    {
        $handleLogic = new HandleLogic;
        $validate = new GoodsBrandValidate();
        $validate_result = $validate->scene($act)->check($param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        if ($act=='del'){
            $param['is_del'] = 1;
            $act = 'edit';
        }
        $arr = $handleLogic->Handle($param,'GoodsBrandModel',$act);
        error:
        return return_json($arr);
    }
}