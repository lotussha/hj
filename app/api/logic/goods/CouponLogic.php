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
 * DateTime: 2020/8/20 下午3:17
 */

namespace app\api\logic\goods;

use app\common\model\coupon\CouponModel;
use app\common\model\coupon\CouponIssueModel;
use sakuno\utils\JsonUtils;
use think\facade\Db;

class CouponLogic
{
    protected $couponModel;
    protected $issueModel;

    public function __construct(CouponModel $couponModel, CouponIssueModel $issueModel)
    {
        $this->couponModel = $couponModel;
        $this->issueModel  = $issueModel;
    }

    /**
     * 领取优惠券
     * @param $param
     */
    public function getCoupon($param)
    {
        //todo 判断接口请求参数
        //******************
        $coupon = $this->couponModel->findInfo(['id' => $param['coupon_id']]);
        if (empty($coupon) || $coupon['receive'] != $param['type']) {
            return JsonUtils::fail('领取失败，优惠券不存在');
        }
        if ($param['type'] == 3 && empty($param['price'])) {
            return JsonUtils::fail('领取失败，请确认订单信息');
        }
        if ($coupon['remaining'] <= 0) {
            return JsonUtils::fail('领取失败，优惠券已发放完毕');
        }
        //检测是否可以领取
        $flag = $this->check_rule($coupon, $param);
        if ($flag) {
            Db::startTrans();
            try {
                $flag = $this->issueModel->insert([
                    'uid'               => $param['uid'],
                    'coupon_id'         => $coupon['id'],
                    'coupon_name'       => $coupon['title'],
                    'coupon_price'      => $coupon['coupon_price'],
                    'start_time'        => time(),
                    'end_time'          => time() + 86400 * $coupon['coupon_time'],
                    'status'            => 1,
                    'use_min_price'     => $coupon['use_min_price'],
                    'receive'           => $param['type'],
                    'goods_category_id' => $coupon['goods_category_id'] ?? 0,
                    'goods_id'          => $coupon['goods_id'] ?? 0,
                ], 1);
                if ($flag >= 1) {
                    Db::name('goods_coupon')->where('id', $coupon['id'])->inc('issued')->dec('remaining')->update();
                    Db::commit();
                    return JsonUtils::successful('领取成功');
                }
            } catch (\Exception $e) {
                Db::rollback();
                return JsonUtils::fail('领取失败，' . $e->getMessage());
            }
        } else {
            return JsonUtils::fail('领取失败，不符合领取条件');
        }
    }

    //判断是否可以领取
    private function check_rule($coupon, $param)
    {
        $res   = 0;
        $count = $this->issueModel->where('uid', $param['uid'])->where('coupon_id', $coupon['id'])->count();
        if ($count < $coupon['available']) {
            switch ($param['type']) {
                case 1://新人注册
                    $res = 1;
                    break;
                case 2://分享（直推）
                    $share_id = Db::name('user')->where('id', $param['uid'])->value('share_id');
                    $count    = Db::name('user')->where('share_id', $share_id)->count();
                    if ($count >= $coupon['receive_additional']) {
                        $res = 1;
                    }
                    break;
                case 3://订单
                    if ($param['price'] >= $coupon['receive_additional']) {
                        $res = 1;
                    }
                    break;
            }
        }
        return $res;
    }

    //获取详情
    public function getInfo($param)
    {
        $info = $this->issueModel
            ->scope('where', $param)
            ->append(['receive_text', 'status_text'])
            ->find()->toArray();
        return JsonUtils::successful('获取成功', $info);
    }

    //获取优惠券列表
    public function getList($param)
    {
        if(!empty($param['goods_id'])){
            $where[] = ['goods_id', '=', $param['goods_id']];
        }elseif(!empty($param['goods_category_id'])){
            $where[] = ['goods_category_id', '=', $param['goods_category_id']];
        }
        $lists = $this->issueModel
            ->scope('where', $param)
            ->where($where)
            ->where('status', '=', 1)
            ->append(['receive_text', 'status_text'])
            ->select()->toArray();
        $data  = ['lists' => $lists];
        return JsonUtils::successful('获取成功', $data);
    }

    //修改优惠券状态
    public function state($param)
    {
        Db::startTrans();
        try {
            if($param['goods_id'] > 0){
                $where[] = ['goods_id', '=', $param['goods_id']];
            }elseif($param['goods_category_id'] > 0){
                $where[] = ['goods_category_id', '=', $param['goods_category_id']];
            }else{
                return JsonUtils::fail('修改失败,请传入正确的商品ID或商品分类ID');
            }
            $issue = $this->issueModel
                ->where(['id' => $param['id'], 'status' => 1, 'is_del' => 0])//todo 加入判断归属uid
                ->where('end_time', '>', time())
                ->where($where)
                ->find();
            if ($issue && $param['price'] >= $issue['use_min_price']) {
                $this->issueModel->where(['id' => $param['id']])->save(['status' => 2]);
                Db::name('goods_coupon')->where(['id' => $issue['coupon_id']])->inc('used')->update();
                Db::name('goods_coupon_log')->insert([
                    'coupon_id' => $issue['coupon_id'],
                    'uid'       => $issue['uid'],
                    'goods_id'  => $param['goods_id'],
                    'use_time'  => time(),
                ]);
                Db::commit();
                return JsonUtils::successful('修改成功');
            }else{
                return JsonUtils::fail('修改失败,未达到优惠券使用条件');
            }
        } catch (\Exception $e) {
            Db::rollback();
            return JsonUtils::fail('修改失败,' . $e->getMessage());
        }
    }

}