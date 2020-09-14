<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/8/12 15:31
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
use app\apiadmin\logic\seckill\SeckillLogic;
use app\common\validate\SeckillValidate;
use sakuno\services\UtilService;
use sakuno\utils\JsonUtils;
use think\Request;

/**
 * 秒杀模块
 * Class Seckill
 * @package app\apiadmin\controller\seckill
 */
class Seckill extends Base
{

    /**
     * 新增数据
     * @param Request $request
     * @return \think\Response
     */
    public function add(Request $request){
        // 获取参数 并校验
        $data = $request->post();
        $validate = new SeckillValidate();
        if(!$validate->scene('add')->check($data)){
            return JsonUtils::fail($validate->getError(),PARAM_IS_INVALID);
        }
        return SeckillLogic::seckillAdd($data);
    }

    /**
     * 查看数据
     * @param Request $request
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function read(Request $request){
        list($id) = UtilService::getMore([
            ['id','']
        ],$request,true);
        $validate = new SeckillValidate();
        if(!$validate->scene('read')->check(['id'=>$id])){
            return JsonUtils::fail($validate->getError(),PARAM_IS_INVALID);
        }
        return SeckillLogic::seckillRead($id);
    }

    /**
     * 编辑数据
     * @param Request $request
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit(Request $request){
        // 获取参数 并校验
        $data = $request->post();
        $validate = new SeckillValidate();
        if(!$validate->scene('edit')->check($data)){
            return JsonUtils::fail($validate->getError(),PARAM_IS_INVALID);
        }
        return SeckillLogic::seckillEdit($data['id'],$data);
    }

    /**
     * 删除数据
     * @param Request $request
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delete(Request $request){
        list($id) = UtilService::getMore([
            ['id','']
        ],$request,true);
        $validate = new SeckillValidate();
        if(!$validate->scene('delete')->check(['id'=>$id])){
            return JsonUtils::fail($validate->getError(),PARAM_IS_INVALID);
        }
        return SeckillLogic::seckillDel($id);
    }

    /**
     * 启用 or 禁用
     * @param Request $request
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function state(Request $request){
        list($id,$status) = UtilService::postMore([
            ['id',''],
            ['status','']
        ],$request,true);
        $validate = new SeckillValidate();
        if(!$validate->scene('state')->check(['id'=>$id,'status'=>$status])){
            return JsonUtils::fail($validate->getError(),PARAM_IS_INVALID);
        }
        return SeckillLogic::seckillState($id,$status);
    }

    /**
     * 数据列表
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
        // 获取分页数据列表
        return SeckillLogic::seckillLists((int)$page,(int)$page_size,(int)$last_id);
    }

}
