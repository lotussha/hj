<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/8/11 11:17
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
use app\apiadmin\logic\cookbook\MarkeringMenuCategoryLogic;
use sakuno\services\UtilService;
use sakuno\utils\JsonUtils;
use think\Request;

/**
 * 菜谱分类
 * Class MarkeringMenuCategory
 * @package app\apiadmin\controller\cookbook
 */
class MarkeringMenuCategory extends Base
{

    /**
     * 新增菜谱分类
     * @param Request $request
     * @return \think\Response
     */
    public function add(Request $request){
        list($category_title) = UtilService::postMore([
            ['category_title','']
        ],$request,true);
        if(empty($category_title)) return JsonUtils::fail('分类不能为空',PARAM_IS_BLANK);
        // 处理数据
        $data['token'] = $this->token; //登录者token
        $data['category_title'] = $category_title;
        $res = MarkeringMenuCategoryLogic::categoryAdd($data);
        if ($res['error']){
            return JsonUtils::fail($res['msg'],$res['code']);
        }
        return JsonUtils::successful('保存成功');
    }

    /**
     * 更新菜谱分类
     * @param Request $request
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit(Request $request){
        // 判断请求方式 获取or修改
        if($request->isPost()){
            // 获取参数
            list($id,$category_title) = UtilService::postMore([
                ['id',0],
                ['category_title','']
            ],$request,true);
            if(empty($id)||empty($category_title)) return JsonUtils::fail('参数错误',PARAM_IS_INVALID);
            // 处理数据
            $data['sort'] = $sort ?? 0;
            $data['category_title'] = $category_title;
            $res = MarkeringMenuCategoryLogic::categoryEdit($id,$data);
            if ($res['error']){
                return JsonUtils::fail($res['msg'],$res['code']);
            }
            return JsonUtils::successful('保存成功');
        } else {
            // 获取参数get
            list($id) = UtilService::getMore([
                ['id',0]
            ],$request,true);
            if(empty($id) || !is_numeric($id)) return JsonUtils::fail('参数错误',PARAM_IS_INVALID);
            $res = MarkeringMenuCategoryLogic::categoryFind($id);
            if ($res['error']){
                return JsonUtils::fail($res['msg'],$res['code']);
            }
            return JsonUtils::successful('获取成功',$res['data']);
        }
    }

    /**
     * 获取菜谱分类列表 - 分页
     * @param Request $request
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function lists(Request $request){
        // 获取参数
        list($page,$page_size,$last_id) = UtilService::getMore([
            ['page',1],
            ['page_size',10],
            ['last_id',0]
        ],$request,true);
        // 获取日志列表
        $res = MarkeringMenuCategoryLogic::categoryListPage((int)$page,(int)$page_size,(int)$last_id);
        if ($res['error']){
            return JsonUtils::fail($res['msg'],$res['code']);
        }
        return JsonUtils::successful('获取成功',$res['data']);
    }

    /**
     * 更改菜谱分类状态 - 开启 or 禁用
     * @param Request $request
     * @return \think\Response
     */
    public function state(Request $request){
        list($id,$status) = UtilService::postMore([
            ['id',0],
            ['status',0]
        ],$request,true);
        if(empty($id)||!is_numeric($id)||empty($status)||!in_array($status,[1,2])) return JsonUtils::fail('参数错误',PARAM_IS_INVALID);
        $res = MarkeringMenuCategoryLogic::categoryStatus($id,$status);
        if ($res['error']){
            return JsonUtils::fail($res['msg'],$res['code']);
        }
        return JsonUtils::successful('操作成功');
    }

    /**
     * 获取菜谱分类列表 - 所有
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function lists_all(){
        $res = MarkeringMenuCategoryLogic::categoryList();
        return JsonUtils::successful('获取成功',$res);
    }

    /**
     * 删除菜谱分类
     * @param Request $request
     * @return \think\Response
     */
    public function delete(Request $request){
        list($id) = UtilService::postMore([
            ['id',0]
        ],$request,true);
        if(empty($id) || !is_numeric($id)) return JsonUtils::fail('参数错误',PARAM_IS_INVALID);
        $res = MarkeringMenuCategoryLogic::categoryDel($id);
        if ($res['error']){
            return JsonUtils::fail($res['msg'],$res['code']);
        }
        return JsonUtils::successful('删除成功');
    }


}
