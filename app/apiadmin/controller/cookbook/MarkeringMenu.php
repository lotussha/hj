<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/8/12 9:52
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
namespace app\apiadmin\controller\cookbook;

use app\apiadmin\controller\Base;
use app\apiadmin\logic\cookbook\MarkeringMenuLogic;
use app\common\validate\MarkeringMenuValidate;
use sakuno\services\UtilService;
use sakuno\utils\JsonUtils;
use think\Request;

class MarkeringMenu extends Base
{

    /**
     * 新增数据
     * @param Request $request
     * @return \think\Response
     */
    public function add(Request $request){
        $data = $request->post();
        $validate = new MarkeringMenuValidate();
        $data['token'] = $this->token; //登录者token
        if(!$validate->scene('add')->check($data)){
            return JsonUtils::fail($validate->getError(),PARAM_IS_INVALID);
        }
        return MarkeringMenuLogic::menuAdd($data);
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
        $data = $request->post();
        $validate = new MarkeringMenuValidate();
        if(!$validate->scene('edit')->check($data)){
            return JsonUtils::fail($validate->getError(),PARAM_IS_INVALID);
        }
        return MarkeringMenuLogic::menuEdit($data['id'],$data);
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
        $validate = new MarkeringMenuValidate();
        if(!$validate->scene('read')->check(['id'=>$id])){
            return JsonUtils::fail($validate->getError(),PARAM_IS_INVALID);
        }
        return MarkeringMenuLogic::menuRead($id);
    }

    /**
     *  删除数据
     * @param Request $request
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delete(Request $request){
        list($id) = UtilService::postMore([
            ['id','']
        ],$request,true);
        $validate = new MarkeringMenuValidate();
        if(!$validate->scene('delete')->check(['id'=>$id])){
            return JsonUtils::fail($validate->getError(),PARAM_IS_INVALID);
        }
        return MarkeringMenuLogic::menuDel($id);
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
        return MarkeringMenuLogic::menuLists((int)$page,(int)$page_size,(int)$last_id);
    }


    /**
     * 开启 or 禁用数据
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
        $validate = new MarkeringMenuValidate();
        if(!$validate->scene('state')->check(['id'=>$id,'status'=>$status])){
            return JsonUtils::fail($validate->getError(),PARAM_IS_INVALID);
        }
        return MarkeringMenuLogic::menuState($id,$status);
    }

    /**
     * 菜谱详情中选择商品信息 -- 商品图片 ID 名字
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function goods_list(){

        $res = MarkeringMenuLogic::goodsList();
        
        return JsonUtils::successful('获取成功',$res);
    }

}
