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
 * DateTime: 2020/8/21 上午11:40
 */

namespace app\api\logic\activity;


use app\common\model\ActivityModel;
use sakuno\utils\JsonUtils;

class PreSaleLogic
{
    protected $model;

    public function __construct(ActivityModel $model)
    {
        $this->model = $model;
    }

    public function getList($param)
    {
        $page      = $param['page'] ?? 1;
        $limitpage = 10;
        $field     = $param['field'] ?? '';
        $lists     = $this->model
            ->field($field)
            ->where(['is_del' => 0, 'status' => 1, 'type' => 3])
            ->scope('where', $param)
            ->page($page, $limitpage)
            ->select()->toArray();
        $data      = ['lists' => $lists];
        return JsonUtils::successful('获取成功', $data);
    }

}