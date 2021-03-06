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
 * DateTime: 2020/8/21 下午1:53
 */

namespace app\api\logic\activity;


use app\common\model\ActivityModel;
use sakuno\utils\JsonUtils;

class RushLogic
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
            ->where(['is_del' => 0, 'status' => 1, 'type' => 4])
            ->scope('where', $param)
            ->page($page, $limitpage)
            ->select()->toArray();
        $data      = ['lists' => $lists];
        return JsonUtils::successful('获取成功', $data);
    }

}