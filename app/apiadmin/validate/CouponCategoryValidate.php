<?php
/**
 * Created by PhpStorm.
 * PHP version 版本号
 *
 * @category 类别名称
 * @package  暂无
 * @author   hj <138610033@qq.com>
 * @license  暂无
 * @link     暂无
 * DateTime: 2020/8/18 上午11:22
 */

namespace app\apiadmin\validate;

use think\facade\Db;
use think\Validate;

class CouponCategoryValidate extends Validate
{
    protected $rule = [
        'id|优惠券分类ID'            => 'require',
        'category_name|优惠券分类名称' => 'require',
        'sort|排序'               => 'number',
    ];

    protected $message = [
    ];

    public function sceneAdd()
    {
        $add = $this->only(['category_name', 'sort'])
                    ->append('sameCate')
                    ->append('category_name', 'sameCate');
        return $add;
    }

    public function sceneEdit()
    {
        $edit = $this->only(['id', 'category_name', 'sort'])
                     ->append('sameCate')
                     ->append('category_name', 'sameCate');
        return $edit;
    }

    public function sceneDel()
    {
        $del = $this->only(['id'])->append('checkCatId');
        return $del;
    }

    protected function sameCate($value, $rule, $data)
    {
        if(empty($data['category_name'])){
            return '优惠券分类名称不能为空';
        }
        $sameCateWhere = ['category_name' => $data['category_name']];
        if(!empty($data['id'])){
            $cat = Db::name('goods_coupon_category')->where($sameCateWhere)->where('id', '<>', $data['id'])->find();
        }else{
            $cat = Db::name('goods_coupon_category')->where($sameCateWhere)->find();
        }
        if ($cat) {
            return '已有相同分类存在';
        } else {
            return true;
        }
    }

    protected function checkCatId($value, $rule, $data)
    {
        $ids = $data['id'];
        // 判断子分类
        // 判断是否存在优惠券
        $goods_count = Db::name('goods_coupon')->where("category_id = {$ids}")->count();
        if ($goods_count > 0) {
            return '该分类下有优惠券不得删除';
        }
        return true;
    }
}