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
 * DateTime: 2020/8/20 下午7:25
 */

namespace app\api\controller\activity;

use app\api\controller\Api;
use app\api\logic\activity\SeckillLogic;
use app\Request;
use sakuno\utils\JsonUtils;
use think\App;

class Seckill extends Api
{
    protected $seckillLogic;

    public function __construct(Request $request, App $app, SeckillLogic $scekillLogic)
    {
        $this->seckillLogic = $scekillLogic;
        parent::__construct($request, $app);
    }

    public function get_list()
    {
        return $this->seckillLogic->getList($this->param);
    }
}