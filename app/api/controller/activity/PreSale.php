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
 * DateTime: 2020/8/21 下午1:54
 */

namespace app\api\controller\activity;

use app\api\controller\Api;
use app\api\logic\activity\PreSaleLogic;
use app\Request;
use sakuno\utils\JsonUtils;
use think\App;

class PreSale extends Api
{
    protected $preSaleLogic;

    public function __construct(Request $request, App $app, PreSaleLogic $preSaleLogic)
    {
        $this->preSaleLogic = $preSaleLogic;
        parent::__construct($request, $app);
    }

    public function get_list()
    {
        return $this->preSaleLogic->getList($this->param);
    }
}