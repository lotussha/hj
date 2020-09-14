<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/26
 * Time: 17:48
 */

namespace app\common\logic\goods;

use think\facade\Db;

class GoodsLogic
{
    /**
     * 筛选的价格期间
     * @param $goods_id_arr|筛选的分类id
     * @param $filter_param
     * @param $action
     * @param int $c 分几段 默认分5 段
     * @return array
     */
    function get_filter_price($goods_id_arr, $filter_param, $action, $c = 6)
    {
        if (!empty($filter_param['price']))
            return array();

        $goods_id_str = implode(',', $goods_id_arr);
        $goods_id_str = $goods_id_str ? $goods_id_str : '0';
        $priceList = Db::name('goods')->where("goods_id", "in", $goods_id_str)->column('shop_price');
        rsort($priceList);
        $max_price = $priceList[0] ?? 0;
        $max_price = (int)$max_price;

        $psize = ceil($max_price / $c); // 每一段累积的价钱
        $parr = array();
        for ($i = 0; $i < $c; $i++) {
            $start = $i * $psize;
            $end = $start + $psize;

            // 如果没有这个价格范围的商品则不列出来
            $in = false;
            foreach ($priceList as $k => $v) {
                if ($v > $start && $v < $end)
                    $in = true;
            }
            if ($in == false)
                continue;

            $filter_param['price'] = "{$start}-{$end}";
            if ($i == 0)
                $parr[] = array('value' => "{$end}元以下");
            elseif($i == ($c-1) && ($max_price > $end))
                $parr[] = array('value' => "{$end}元以上");
            else
                $parr[] = array('value' => "{$start}-{$end}元");
        }
        return $parr;
    }

    /**
     * 获取某个商品分类的 儿子 孙子  重子重孙 的 id
     * @param $cat_id
     * @return array|mixed
     * User: Jomlz
     */
    public function getCatGrandson ($cat_id)
    {
        $GLOBALS['catGrandson'] = array();
        $GLOBALS['category_id_arr'] = array();
        // 先把自己的id 保存起来
        $GLOBALS['catGrandson'][] = $cat_id;
        // 把整张表找出来
//        $GLOBALS['category_id_arr'] =  Db::name('goods_category')->cache(true,3600)->column('id,parent_id');
        $GLOBALS['category_id_arr'] =  Db::name('goods_category')->column('id,parent_id');
        // 先把所有儿子找出来
//        $son_id_arr = Db::name('goods_category')->where("parent_id", $cat_id)->cache(true,3600)->column('id');
        $son_id_arr = Db::name('goods_category')->where("parent_id", $cat_id)->column('id');
        foreach($son_id_arr as $k => $v)
        {
            $this->getCatGrandson2($v);
        }
        return $GLOBALS['catGrandson'];
    }

    /**
     * 递归调用找到 重子重孙
     * @param type $cat_id
     */
    public function getCatGrandson2($cat_id)
    {
        $GLOBALS['catGrandson'][] = $cat_id;
        foreach($GLOBALS['category_id_arr'] as $k => $v)
        {
            // 找到孙子
            if($v['parent_id'] == $cat_id)
            {
                $this->getCatGrandson2($v['id']); // 继续找孙子
            }
        }
    }

    /**
     * @param  $brand_id|帅选品牌id
     * @param  $price|帅选价格
     * @return array|mixed
     */
    function getGoodsIdByBrandPrice($brand_id, $price)
    {
        if (empty($brand_id) && empty($price))
            return array();
        $brand_select_goods=$price_select_goods=array();
        if ($brand_id) // 品牌查询
        {
            $brand_id_arr = explode('_', $brand_id);
            $brand_select_goods = Db::name('goods')->whereIn('brand_id',$brand_id_arr,'or')->column('goods_id');
        }
        if ($price)// 价格查询
        {
            $price = explode('-', $price);
            $price[0] = intval($price[0]);
            $price[1] = intval($price[1]);
            $price_where=" shop_price >= $price[0] and shop_price <= $price[1] ";
            $price_select_goods = Db::name('goods')->where($price_where)->column('goods_id');
        }
        if($brand_select_goods && $price_select_goods)
            $arr = array_intersect($brand_select_goods,$price_select_goods);
        else
            $arr = array_merge($brand_select_goods,$price_select_goods);
        return $arr ? $arr : array();
    }
    /**
     * 筛选条件菜单
     * @param $filter_param
     * @param $action
     * @return array
     */
    function get_filter_menu($filter_param, $action)
    {
        $menu_list = array();
        // 品牌
        if (!empty($filter_param['brand_id'])) {
            $brand_list = Db::name('goods_brand')->column('id,name');
            $brand_id = explode(',', $filter_param['brand_id']);
            $brand['text'] = "品牌:";
            foreach ($brand_id as $k => $v) {
                foreach ($brand_list as $kk=>$vv){
                    if ($v == $vv['id']){
                        $brand['text'] .= $vv['id'] . ',';
                    }
                }
            }
            $brand['text'] = substr($brand['text'], 0, -1);
            $tmp = $filter_param;
            unset($tmp['brand_id']); // 当前的参数不再带入
            $menu_list[] = $brand;
        }
        // 规格
        if (!empty($filter_param['spec'])) {
            $spec_arr = Db::name('goods_spec')->column('id,name');
            $spec = [];
            foreach ($spec_arr as $k=>$v){
                $spec[$v['id']] = $v;
            }
            $spec_item = Db::name('goods_spec_item')->column('id,item');
            $spec_group = explode('@', $filter_param['spec']);
            foreach ($spec_group as $k => $v) {
                $spec_group2 = explode('_', $v);
                $spec_menu['text'] = $spec[$spec_group2[0]]['name'] . ':';
                array_shift($spec_group2); // 弹出第一个规格名称
                foreach ($spec_group2 as $k2 => $v2) {
                    $spec_menu['text'] .= $spec_item[$v2]['item'] . ',';
                }
                $spec_menu['text'] = substr($spec_menu['text'], 0, -1);

                $tmp = $spec_group;
                $tmp2 = $filter_param;
                unset($tmp[$k]);
                $tmp2['spec'] = implode('@', $tmp); // 当前的参数不再带入
                $menu_list[] = $spec_menu;
            }
        }
        // 属性
        if (!empty($filter_param['attr'])) {
            $goods_attribute_arr = Db::name('goods_attribute')->column('attr_id,attr_name');
            $goods_attribute = [];
            foreach ($goods_attribute_arr as $k=>$v){
                $goods_attribute[$v['attr_id']] = $v;
            }
            $attr_group = explode('@', $filter_param['attr']);
            foreach ($attr_group as $k => $v) {
                $attr_group2 = explode('_', $v);
                $attr_menu['text'] = $goods_attribute[$attr_group2[0]]['attr_name'] . ':';
                array_shift($attr_group2); // 弹出第一个规格名称
                foreach ($attr_group2 as $k2 => $v2) {
                    $attr_menu['text'] .= $v2 . ',';
                }
                $attr_menu['text'] = substr($attr_menu['text'], 0, -1);

                $tmp = $attr_group;
                $tmp2 = $filter_param;
                unset($tmp[$k]);
                $tmp2['attr'] = implode('@', $tmp); // 当前的参数不再带入
                $menu_list[] = $attr_menu;
            }
        }
        // 价格
        if (!empty($filter_param['price'])) {
            $price_menu['text'] = "价格:" . $filter_param['price'];
            unset($filter_param['price']);
            $menu_list[] = $price_menu;
        }
        return $menu_list;
    }

    /**
     * 获取 商品列表页帅选品牌
     * User: Jomlz
     */
    public function get_filter_brand($goods_id_arr, $filter_param)
    {
        if (!empty($filter_param['brand_id'])) {
            return array();
        }

        $map = [['goods_id','in', $goods_id_arr],['brand_id','>',0]];
        $brand_id_arr = Db::name('goods')->where($map)->column('brand_id');
        $list_brand = Db::name('goods_brand')
            ->field('id,name,logo,cat_id')
            ->where([['id','in',$brand_id_arr],['is_hot','=',1],['is_del','=',0]])
            ->limit('30')
            ->select();
        foreach ($list_brand as $k => $v) {
            // 帅选参数
            $filter_param['brand_id'] = $v['id'];
        }
        return $list_brand;
    }

    /**
     *网站自营,入驻商家,货到付款,仅看有货,促销商品
     * @param $sel|筛选条件
     * @param array $cat_id|分类ID
     * @return mixed
     */
    function getFilterSelected($sel ,$cat_id=[]){
        $where =[];
        if($cat_id){
            $cat_ids = implode(',', $cat_id);
            $where[]=['cat_id','in',$cat_ids];
        }
//        //促销商品
//        if($sel == 'prom_type'){
//            $where[] = ['prom_type','=',3];
//        }
        //看有货
        if($sel == 'store_count'){
            $where[] = ['store_count','>',0];
        }
        //看包邮
        if($sel == 'free_post'){
            $where[] = ['is_free_shipping','>',0];
        }
        //看全部
        if($sel == 'all'){
            $arr_id = Db::name('goods')->where($where)->column('goods_id');
        }else{
            $arr_id = Db::name('goods')->where($where)->column('goods_id');
        }
        return $arr_id;
    }

    /**
     * 根据规格 查找 商品id
     * @param $spec|规格
     * @return array|\type
     */
    function getGoodsIdBySpec($spec)
    {
        if (empty($spec))
            return array();

        $spec_group = explode('@', $spec);
        $where = " where 1=1 ";
        foreach ($spec_group as $k => $v) {
            $spec_group2 = explode('_', $v);
            array_shift($spec_group2);
            $like = array();
            foreach ($spec_group2 as $k2 => $v2) {
                $v2 = addslashes($v2);
                $like[] = " key2  like '%\_$v2\_%' ";
            }
            $where .= " and (" . implode('or', $like) . ") ";
        }
        $sql = "select * from (select *,concat('_',`key`,'_') as key2 from rh_goods_spec_price as a) b  $where";
        $result = Db::query($sql);
        $arr = get_arr_column($result, 'goods_id');  // 只获取商品id 那一列
        return ($arr ? $arr : array_unique($arr));
    }

    /**
     * @param $goods_id_arr
     * @param $filter_param
     * @param $action
     * @param int $mode  0  返回数组形式  1 直接返回result
     * @return array 这里状态一般都为1 result 不是返回数据 就是空
     * 获取 商品列表页帅选规格
     */
    public function get_filter_spec($goods_id_arr, $filter_param, $action, $mode = 0)
    {
        $goods_id_str = implode(',', $goods_id_arr);
        $goods_id_str = $goods_id_str ? $goods_id_str : '0';
        $spec_key = DB::query("select group_concat(`key` separator  '_') as `key` from rh_goods_spec_price where goods_id in($goods_id_str) and is_del=0 and prom_id=0");  //where("goods_id in($goods_id_str)")->select();
        $spec_key = explode('_', $spec_key[0]['key']);
        $spec_key = array_unique($spec_key);
        $spec_key = array_filter($spec_key);
        if (empty($spec_key)) {
            if ($mode == 1) return array();
            return array('status' => 1, 'msg' => '', 'result' => array());
        }
//        $spec = Db::name('goods_spec')->where(array('search_index'=>1))->column('id,name');
        $spec_arr = Db::name('goods_spec')->column('id,name');
        $spec = [];
        foreach ($spec_arr as $k=>$v){
            $spec[$v['id']] =  $v;
        }
        $spec_item_arr =  Db::name('goods_spec_item')->where('spec_id','in',array_column($spec,'id'))->column('id,spec_id,item');
        $spec_item = [];
        foreach ($spec_item_arr as $k=>$v){
            $spec_item[$v['id']] =  $v;
        }
        $list_spec = array();
        $old_spec = $filter_param['spec'] ?? '';
        foreach ($spec_key as $k => $v) {
            if (strpos($old_spec, $spec_item[$v]['spec_id'] . '_') === 0 || strpos($old_spec, '@' . $spec_item[$v]['spec_id'] . '_') || $spec_item[$v]['spec_id'] == '')
                continue;
//            $list_spec[$spec_item[$v]['spec_id']]['spec_id'] = $spec_item[$v]['spec_id'];
            $list_spec[$spec_item[$v]['spec_id']]['name'] = $spec[$spec_item[$v]['spec_id']];
            //$list_spec[$spec_item[$v]['spec_id']]['item'][$v] = $spec_item[$v]['item'];

            // 帅选参数
            if (!empty($old_spec))
                $filter_param['spec'] = $old_spec . '@' . $spec_item[$v]['spec_id'] . '_' . $v;
            else
                $filter_param['spec'] = $spec_item[$v]['spec_id'] . '_' . $v;
            $list_spec[$spec_item[$v]['spec_id']]['item'][] = array('key' => $spec_item[$v]['spec_id'], 'val' => $v, 'item' => $spec_item[$v]['item']);
        }
        if ($mode == 1) return array_values($list_spec);
        return array('status' => 1, 'msg' => '', 'result' => array_values($list_spec));
    }

    /**
     * @param array $goods_id_arr
     * @param $filter_param
     * @param $action
     * @param int $mode 0  返回数组形式  1 直接返回result
     * @return array
     * 获取商品列表页帅选属性
     */
    public function get_filter_attr($goods_id_arr = array(), $filter_param, $action, $mode = 0)
    {
        $goods_id_str = implode(',', $goods_id_arr);
        $goods_id_str = $goods_id_str ? $goods_id_str : '0';
        $goods_attr =  Db::name('goods_attr')->where([['goods_id','in',$goods_id_str],['attr_value','<>','']])->select();
//        $goods_attribute_arr =  Db::name('goods_attribute')->where("attr_index = 1")->column('attr_id,attr_name,attr_index');
        $goods_attribute_arr =  Db::name('goods_attribute')->column('attr_id,attr_name,attr_index');
        $goods_attribute = [];
        foreach ($goods_attribute_arr as $k=>$v){
            $goods_attribute[$v['attr_id']] =  $v;
        }
        if (empty($goods_attr)) {
            if ($mode == 1) return array();
            return array('status' => 1, 'msg' => '', 'result' => array());
        }
        $list_attr = $attr_value_arr = array();
        $old_attr = $filter_param['attr'] ?? '';
        foreach ($goods_attr as $k => $v) {
            // 存在的帅选不再显示
            if (strpos($old_attr, $v['attr_id'] . '_') === 0 || strpos($old_attr, '@' . $v['attr_id'] . '_'))
                continue;
//            if ($goods_attribute[$v['attr_id']]['attr_index'] == 0)
//                continue;
            $v['attr_value'] = trim($v['attr_value']);
            // 如果同一个属性id 的属性值存储过了 就不再存贮
            $attr_value_arr[$v['attr_id']] = $attr_value_arr[$v['attr_id']] ?? [];
            if (in_array($v['attr_id'] . '_' . $v['attr_value'], (array)$attr_value_arr[$v['attr_id']]))
                continue;
            $attr_value_arr[$v['attr_id']][] = $v['attr_id'] . '_' . $v['attr_value'];

            $list_attr[$v['attr_id']]['attr_id'] = $v['attr_id'];
            $list_attr[$v['attr_id']]['attr_name'] = $goods_attribute[$v['attr_id']]['attr_name'] ?? [];

            // 帅选参数
            if (!empty($old_attr))
                $filter_param['attr'] = $old_attr . '@' . $v['attr_id'] . '_' . $v['attr_value'];
            else
                $filter_param['attr'] = $v['attr_id'] . '_' . $v['attr_value'];

            $list_attr[$v['attr_id']]['attr_value'][] = array('key' => $v['attr_id'], 'val' => $v['attr_value'], 'attr_value' => $v['attr_value']);
            //unset($filter_param['attr_id_'.$v['attr_id']]);
        }
        if ($mode == 1) return array_values($list_attr);
        return array('status' => 1, 'msg' => '', 'result' => $list_attr);
    }

    /**
     * @param $attr|属性
     * @return array|mixed
     * 根据属性 查找 商品id
     * 59_直板_翻盖
     * 80_BT4.0_BT4.1
     */
    function getGoodsIdByAttr($attr)
    {
        if (empty($attr))
            return array();

        $attr_group = explode('@', $attr);
        $attr_id = $attr_value = array();
        foreach ($attr_group as $k => $v) {
            $attr_group2 = explode('_', $v);
            $attr_id[] = array_shift($attr_group2);
            $attr_value = array_merge($attr_value, $attr_group2);
        }
        $c = count($attr_id) - 1;
        if ($c > 0) {
            $arr_arr = Db::name('goods_attr')
                ->where(['attr_id'=>['in',$attr_id],'attr_value'=>['in',$attr_value]])
                ->group('goods_id')
                ->having("count(goods_id) > $c")
                ->column("goods_id");
        }else{
            $arr_arr = Db::name('goods_attr')
                ->where([['attr_id','in',$attr_id],['attr_value','in',$attr_value]])
                ->column("goods_id,attr_value"); // 如果只有一个条件不再进行分组查询
        }
        $arr = [];
        foreach ($arr_arr as $k=>$v){
            $arr[$v['goods_id']] =  $v['goods_id'];
        }
        return ($arr ? $arr : array_unique($arr));
    }

    /**
     * 活动商品
     * @param string $prom_type
     * @return array
     * User: Jomlz
     */
    public function getPromGoods($prom_type='')
    {
        if (empty($prom_type))
            return array();
        $list = Db::name('goods')->alias('g')
            ->where([['g.is_on_sale','=',1],['g.is_check','=',1],['g.prom_type','=',$prom_type]])
            ->join('activity a','g.prom_id = a.id')
            ->column('g.goods_id');
        $arr = [];
        foreach ($list as $k=>$v){
            $arr[$v] =  $v;
        }
        return ($arr ?? array_unique($arr));
    }
}