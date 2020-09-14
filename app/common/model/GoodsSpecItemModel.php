<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/4
 * Time: 14:43
 */

namespace app\common\model;

use think\Model;

class GoodsSpecItemModel extends Model
{
    protected $name = 'goods_spec_item';

    public function afterSave($data)
    {
        $post_items =  explode(',',$data['item']);
        foreach ($post_items as $key=>$val)
        {
            if (empty($val)){
                unset($post_items[$key]);
            }else{
                $post_items[$key] = $val;
            }
        }
        $old_items = self::where("spec_id=".$data['spec_id'])->select();
        $db_items = [];
        foreach ($old_items as $k=>$v){
            $db_items[$v['id']] = $v['item'];
        }

        /* 提交过来的 跟数据库中比较 不存在 插入*/
        $dataList = [];
        foreach($post_items as $key => $val)
        {
            if(!in_array($val, $db_items))
                $dataList[] = array('spec_id'=>$data['spec_id'],'item'=>$val);
        }

        // 批量添加数据
        $dataList && self::insertAll($dataList);

        /* 数据库中的 跟提交过来的比较 不存在删除*/
        foreach($db_items as $key => $val)
        {
            if(!in_array($val, $post_items))
            {
//                db("SpecGoodsPrice")->where("`key` REGEXP '^{$key}_' OR `key` REGEXP '_{$key}_' OR `key` REGEXP '_{$key}$' or `key` = '{$key}'")->delete(); // 删除规格项价格表
                self::where('id='.$key)->delete(); // 删除规格项
            }
        }

    }
}