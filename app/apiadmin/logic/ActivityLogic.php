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
 * DateTime: 2020/8/19 上午11:51
 */

namespace app\apiadmin\logic;

use app\common\logic\HandleLogic;
use app\common\model\ActivityModel;
use app\common\model\GoodsModel;
use app\common\model\GoodsSpecPriceModel;
use sakuno\utils\JsonUtils;
use think\facade\Db;

class ActivityLogic
{
    /**
     * 获取活动列表
     * @param int $page
     * @param int $list_rows
     * @param $param
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getActivityList($param)
    {
        $is_del        = $param['is_del'] ?? 0;
        $field         = $param['field'] ?? '';
        $activityModel = new ActivityModel();
        $lists         = $activityModel
            ->field($field)
            ->scope('where', $param)
            ->where('is_del', $is_del)
            ->order('sort desc')
            ->append(['start_time_data', 'end_time_data', 'status_text'])
            ->paginate(10);
        return $lists->toArray();
    }

    /**
     * 活动操作
     * @param array $param
     * @param string $act
     * @return array|\think\Response
     */
    public function activityHandel($param = [], $act = '')
    {
        $goods_arr     = [];
        $res           = array_return();
        $handleLogic   = new HandleLogic();
        $activityModel = new ActivityModel();
        // 启动事务
        Db::startTrans();
        try {
            if (isset($param['id']) && !empty($param['id'])) {
//                $flag = $this->in_progress($param['id']);
//                if($flag){
//                    $res['status'] = 0;
//                    $res['msg']    = "活动正在进行中，禁止修改";
//                    return $res;
//                }
                $res = $handleLogic->Handle($param, 'ActivityModel', 'edit', 'id');
            } else {
                $param['add_time'] = time();
                $param['end_time'] = $param['start_time'] + 3600;
                $res               = $handleLogic->Handle($param, 'ActivityModel', 'add', 'id');
            }
            if ($res['status'] == 1) {
                Db::name('goods_spec_price')->where('prom_id', $res['object_id'])->where('prom_type', $param['type'])->update(['is_del' => 1]);
                $common = json_decode($param['goods_common'], 1);
                if (is_array($common)) {
                    $goods_num      = 0;
                    $check_goods = current($common);
                    $goods_price = $check_goods[$activityModel->priceText($param['type']) . '_price'];
                    if (!empty($check_goods)) {
                        $check_goods_id = $check_goods['goods_id'];
                        $min_price = $goods_price;
                    } else {
                        $res['status'] = 0;
                        $res['msg']    = "商品规格信息有误";
                        return $res;
                    }
                    unset($goods_price);
                    foreach ($common as $key => $value) {
                        $value['prom_id']   = $res['object_id'];
                        $value['prom_type'] = $param['type'];
                        if (!empty($value['goods_id'])) {
                            $goods_model = new GoodsModel();
                            $goods       = $goods_model->where('goods_id', $value['goods_id'])->find();
                            if (!$goods) {
                                $res['status'] = 0;
                                $res['msg']    = "商品信息有误";
                                return $res;
                            } else {
                                if ($check_goods_id == $value['goods_id']) {
                                    $goods_num += $value['store_count'];
                                    $goods_price = $value[$activityModel->priceText($param['type']) . '_price'];
                                    if ($goods_price < $min_price) {
                                        $min_price = $goods_price;
                                    }
                                } else {
                                    $min_price = 0;
                                    $goods_num = 0;
                                }
                                $goods_arr[]      = $value['goods_id'];
                                $goods->prom_id   = $res['object_id'];
                                $goods->prom_type = $param['type'];
                                $goods->save();
                                $value['key'] = $key;
                                $handleLogic->Handle($value, 'GoodsSpecPriceModel', 'add', 'item_id');
                            }
                        } else {
                            $res['status'] = 0;
                            $res['msg']    = "商品ID不能为空";
                            return $res;
                        }
                    }
                    if($min_price > 0 && $goods_num > 0){
                        $activityModel->where('id', $res['object_id'])->save(['goods_num' => $goods_num, 'goods_price' => $min_price]);
                    }
                } else {
                    $res['status'] = 0;
                    $res['msg']    = "商品规格信息有误";
                    return $res;
                }
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            $res['status'] = 0;
            $res['msg']    = "提交活动失败," . $e->getMessage();
            Db::rollback();
        }
        return $res;
    }

    public function getInfo($param)
    {
        $is_del      = $param['is_del'] ?? 0;
        $a_field     = $param['a_field'] ?? '';
        $g_field     = $param['g_field'] ?? '';
        $model       = new ActivityModel;
        $activity    = $model
            ->where('id', $param['id'])
            ->where('is_del', $is_del)
            ->field($a_field)
            ->append(['start_time_data', 'end_time_data', 'status_text'])
            ->find();
        $goods_model = new GoodsSpecPriceModel();
        $common      = $goods_model
            ->where('prom_id', $param['id'])
            ->where('is_del', $is_del)
            ->field($g_field)
            ->select();
        $common      = $common->toArray();
        foreach ($common as &$value) {
            if ($value['final_payment_time']) {
                $value['final_payment_time'] = date('Y-m-d H:i:s', $value['final_payment_time']);
            }
        }
        $activity['goods_common'] = $common;
        return json($activity->toArray());
    }

    /**
     * 判断活动是否在进行中
     */
    private function in_progress($id)
    {
        $flag = Db::name('activity')
                  ->where('id', $id)
                  ->where('status', 1)
                  ->where('is_del', 0)
                  ->where('start_time', '<=', time())
                  ->where('end_time', '>=', time())
                  ->find();
        return $flag ? 1 : 0;
    }

    /**
     * 删除活动
     * @param $id
     * @return \think\Response
     * @throws \think\db\exception\DbException
     */
    public function delActivity($id)
    {
        Db::name('activity')->where('id', $id)->update(['is_del' => 1]);
        Db::name('goods')->whereIn('prom_id', $id)->update(['prom_id' => 0, 'prom_type' => 0]);
        Db::name('goods_spec_price')->whereIn('prom_id', $id)->update(['is_del' => 1]);
        return JsonUtils::successful('操作成功');
    }

    /**
     * 改变活动状态
     * @param $id
     * @return \think\Response
     * @throws \think\db\exception\DbException
     */
    public function stateActivity($id)
    {
        $status = Db::name('activity')->where('id', $id)->value('status');
        Db::name('activity')->where('id', $id)->update(['status' => abs($status - 1)]);
        return JsonUtils::successful('操作成功');
    }
}