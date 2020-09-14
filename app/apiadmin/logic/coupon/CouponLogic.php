<?php

namespace app\apiadmin\logic\coupon;

use app\common\logic\HandleLogic;
use app\common\model\coupon\CouponModel;
use sakuno\utils\JsonUtils;
use think\facade\Db;

class CouponLogic
{
    /**
     * @param int $page
     * @param int $list_rows
     * @param $param
     * @return mixed
     */
    public function getCouponList($page = 1, $list_rows = 10, $param)
    {
        $is_del      = $param['is_del'] ?? 0;
        $field       = $param['field'] ?? '';
        $couponModel = new CouponModel();
        $lists = $couponModel
            ->field($field)
            ->scope('where', $param)
            ->where('is_del', $is_del)
            ->page($page, $list_rows)
            ->order('sort desc')
            ->select();
        foreach ($lists as &$info){
            $info['add_time'] = date('Y-m-d H:i:s', $info['add_time']);
        }
        return arrString($lists->toArray());
    }

    /**
     * @param array $param
     */
    public function couponHandel($param = [])
    {
        $res         = array_return();
        $handleLogic = new HandleLogic();
        $couponModel = new CouponModel();
        // 启动事务
        Db::startTrans();
        try {
            $goods = Db::name('goods')->where('goods_id', $param['goods_id'])->find();
            if (!$goods) {
                $res['status'] = 0;
                $res['msg']    = '商品不存在';
                return $res;
            }
            $category = Db::name('goods_coupon_category')->where('id', $param['category_id'])->find();
            if (!$category) {
                $res['status'] = 0;
                $res['msg']    = '分类不存在';
                return $res;
            }
            if (isset($param['id']) && !empty($param['id'])) {
                $couponInfo = $couponModel->where(['id' => $param['id']])->find();
                if (empty($couponInfo)) {
                    $res['status'] = 0;
                    $res['msg']    = '优惠券不存在';
                    return $res;
                }
                $res = $handleLogic->Handle($param, 'app\common\model\coupon\CouponModel', 'edit', 'id');
            } else {
                $param['add_time'] = time();
                $param['remaining'] = $param['quantity'];
                $res               = $handleLogic->Handle($param, 'app\common\model\coupon\CouponModel', 'add', 'id');
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            $res['status'] = 0;
            $res['msg']    = "提交优惠券失败," . $e->getMessage();
            Db::rollback();
        }
        return $res;
    }

    /**
     * @param $id
     * @return \think\Response
     * @throws \think\db\exception\DbException
     */
    public function delCoupon($id)
    {
        Db::name('goods_coupon')->whereIn('id', $id)->update(['is_del' => 1]);
        return JsonUtils::successful('操作成功');
    }
}
