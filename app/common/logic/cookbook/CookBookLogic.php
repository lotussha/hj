<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/26
 * Time: 17:48
 */

namespace app\common\logic\cookbook;

use think\facade\Db;

class CookBookLogic
{
    
    /**
     * 获取菜谱分类
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
        $GLOBALS['category_id_arr'] =  Db::name('goods_category')->cache(true,3600)->column('id,parent_id');
        // 先把所有儿子找出来
        $son_id_arr = Db::name('goods_category')->where("parent_id", $cat_id)->cache(true,3600)->column('id');
        foreach($son_id_arr as $k => $v)
        {
            $this->getCatGrandson2($v);
        }
        return $GLOBALS['catGrandson'];
    }

}