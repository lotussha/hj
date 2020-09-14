<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/8/10 10:19
 *                ..::::::::::::.                                女神保佑，代码无bug！！！
 *              ``::::::::::::::::                               Codes are far away from bugs with the goddess！！！
 *               ::::``:::::::::'        .:::.
 *              ::::'   ':::::'       .::::::::.
 *            .::::'      ::::     .:::::::'::::.
 *           .:::'       :::::  .:::::::::' ':::::.
 *          .::'        :::::.:::::::::'      ':::::.
 *         .::'         ::::::::::::::'         ``::::.
 *     ...:::           ::::::::::::'              ``::.
 *    ````':.          ':::::::::'                  ::::..
 *                       '.:::::'                    ':'````..
 *
 */
namespace app\apiadmin\controller\log;

use app\apiadmin\controller\Base;
use app\apiadmin\logic\log\GoodsLogLogic;
use sakuno\services\UtilService;
use sakuno\utils\JsonUtils;
use think\Request;

/**
 * 商品日志模块
 * Class GoodsLog
 * @package app\apiadmin\controller\log
 */
class GoodsLog extends Base
{

    /**
     * 获取商品日志列表(根据商品id)
     * @param Request $request
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function lists_by_goods(Request $request){
        list($goods_id) = UtilService::getMore([
            ['goods_id',0]
        ],$request,true);
        // 获取日志列表
        $res = (new GoodsLogLogic())->goodsLogListByGoodsId((int)$goods_id);
        if ($res['error']){
            return JsonUtils::fail($res['msg'],$res['code']);
        }
        return JsonUtils::successful('获取成功',$res['data']);
    }

    /**
     * 获取商品日志列表(分页)
     * @param Request $request
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function lists(Request $request){
        list($page,$page_size,$last_id) = UtilService::getMore([
            ['page',1],
            ['page_size',10],
            ['last_id',0]
        ],$request,true);
        // 获取日志列表
        $res = (new GoodsLogLogic())->goodsLogListPage((int)$page,(int)$page_size,(int)$last_id);
        if ($res['error']){
            return JsonUtils::fail($res['msg'],$res['code']);
        }
        return JsonUtils::successful('获取成功',$res['data']);
    }

}