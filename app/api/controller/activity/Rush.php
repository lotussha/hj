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
 * DateTime: 2020/8/21 下午1:52
 */

namespace app\api\controller\activity;

use app\api\controller\Api;
use app\api\logic\activity\RushLogic;
use app\Request;
use sakuno\utils\JsonUtils;
use think\App;

class Rush extends Api
{
    protected $rushLogic;

    public function __construct(Request $request, App $app, RushLogic $rushLogic)
    {
        $this->rushLogic = $rushLogic;
        parent::__construct($request, $app);
    }

    public function get_list()
    {
        return $this->rushLogic->getList($this->param);
    }
}