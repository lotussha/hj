<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/8/11 15:40
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
namespace app\apiadmin\controller\seckill;

use app\apiadmin\controller\Base;
use app\apiadmin\logic\seckill\SeckillConfigLogic;
use sakuno\services\UtilService;
use think\Request;

/**
 * 秒杀系统配置
 * Class SeckillConfig
 * @package app\apiadmin\controller\seckill
 */
class SeckillConfig extends Base
{

    /**
     * 新增数据
     * @param Request $request
     * @return \think\Response
     */
    public function add(Request $request){
        // 获取参数
        list($sort,$hours,$time_start) = UtilService::postMore([
            ['sort',0],
            ['hours',0],
            ['time_start',0]
        ],$request,true);
        return SeckillConfigLogic::configAdd($sort,$hours,$time_start);
    }

    /**
     * 更新数据 or 查看数据
     * @param Request $request
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit(Request $request){
        // 判断请求方式
        if($request->isPost()){
            // 获取参数
            list($id,$sort,$hours,$time_start) = UtilService::postMore([
                ['id',0],
                ['sort',0],
                ['hours',0],
                ['time_start',0]
            ],$request,true);
            return SeckillConfigLogic::configEdit($id,$sort,$hours,$time_start);
        } else {
            // 获取请求参数
            list($id) = UtilService::getMore([
                ['id',0]
            ],$request,true);
            return SeckillConfigLogic::configFind($id);
        }
    }

    /**
     * 删除数据
     * @param Request $request
     * @return \think\Response
     */
    public function delete(Request $request){
        list($id) = UtilService::postMore([
            ['id',0]
        ],$request,true);
        return SeckillConfigLogic::configDel($id);
    }

    /**
     * 更改数据状态 开启 or 禁用
     * @param Request $request
     * @return \think\Response
     */
    public function state(Request $request){
        list($id,$status) = UtilService::postMore([
            ['id',0],
            ['status',0],
        ],$request,true);
        return SeckillConfigLogic::categoryStatus($id,$status);
    }

    /**
     * 获取数据列表 - all
     * @param Request $request
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function lists(Request $request){
        // 获取参数
        list($is_title) = UtilService::getMore([
            ['is_title',0]
        ],$request,true);
        return SeckillConfigLogic::categoryLists($is_title);
    }

}
