<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/8/11 11:39
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
namespace app\apiadmin\logic\cookbook;

use app\common\model\cookbook\MarkeringMenuCategoryModel;
use sakuno\utils\JsonUtils;
use app\Request;
use app\apiadmin\model\AdminUsers;

/**
 * 菜谱分类logic
 * Class MarkeringMenuCategoryLogic
 * @package app\common\logic\cookbook
 */
class MarkeringMenuCategoryLogic
{

    /**
     * 新增菜谱分类
     * @param $data
     * @return array
     */
    public static function categoryAdd($data){
        //获取登录的真实token 存入相关日志
        $checkToken = checkToken($data['token']);
        $admin = (new AdminUsers())->where(['id'=>$checkToken['data']->admin_id])->find();
        
        // TOKEN 获取当前操作者身份和ID 
        $data['opt_source'] = $admin['identity']; // 身份：1:平台2:供货商3:门店4:团长5：仓库
        $data['operator']   = $admin['id'];       // 操作者ID
        try{
            $res = (new MarkeringMenuCategoryModel())->addInfo($data);
        }catch (\Exception $e){
            return JsonUtils::returnDataErr($e->getMessage(),DATA_IS_WRONG);
        }
        if(!$res){
            return JsonUtils::returnDataErr('保存失败');
        } else {
            return JsonUtils::returnDataSuc('保存成功');
        }
    }


    /**
     * 更新分类数据
     * @param int $id
     * @param array $data
     * @return array
     */
    public static function categoryEdit(int $id,array $data){
        // TODO 这里有需要的话 最好校验一下用户身份是否可以查看当前数据 尽量中间件校验
        // 更新数据 并且捕获异常
        try{
            $res  = (new MarkeringMenuCategoryModel())->updateInfo(['id'=>$id],$data);
        }catch (\Exception $e){
            return JsonUtils::returnDataErr($e->getMessage(),DATA_IS_WRONG);
        }
        if(!$res){
            return JsonUtils::returnDataErr('保存失败');
        } else {
            return JsonUtils::returnDataSuc('保存成功');
        }
    }

    /**
     * 根据分类id获取分类
     * @param int $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function categoryFind(int $id){
        // 获取数据
        $res = (new MarkeringMenuCategoryModel())->findInfo(['id'=>$id,'is_delete'=>config('status.mysql.table_not_delete')]);
        if(empty($res)) return JsonUtils::returnDataErr('数据不存在',RESULE_DATA_NONE);
        // TODO 这里有需要的话 最好校验一下用户身份是否可以查看当前数据 尽量中间件校验

        // 返回数据
        return JsonUtils::returnDataSuc('获取成功',SUCCESS,$res);
    }


    /**
     * 分页获取数据列表
     * @param int|null $page
     * @param int|null $page_size
     * @param int|null $last_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function categoryListPage(int $page = null,int $page_size = null,int $last_id = null){
        if(empty(($page))){
            return JsonUtils::returnDataErr('参数异常',PARAM_IS_INVALID);
        }
        // 查询条件
        $where[] = ['is_delete','=',config('status.mysql.table_not_delete')];
        if ($page > 1){
            !empty($last_id) && $where[] = ['id','<',$last_id];
        }
        // 获取数据列表 优化 
        $res = (new MarkeringMenuCategoryModel())->getLimitData($where,$page_size,'',['id'=>'desc'],['is_delete','delete_time']);
        // 获取列表总量
        $total = (new MarkeringMenuCategoryModel())->statInfo($where);
        $page_total = ceil($total/$page_size);
        $data = ['list'=>$res,'total'=>$total,'page'=>$page,'page_total'=>$page_total,'last_id'=>array_pop($res)['id']];
        return JsonUtils::returnDataSuc('获取成功',SUCCESS,$data);
    }

    /**
     * 获取所有分类
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function categoryList(){
        $where[] = ['is_delete','=',config('status.mysql.table_not_delete')];
        $res = (new MarkeringMenuCategoryModel())->getList($where,'',['id'=>'desc'],['is_delete','delete_time']);
        return $res;
    }

    /**
     * 删除数据
     * @param int $id
     * @return array
     */
    public static function categoryDel(int $id){
        // TODO 这里有需要的话 最好校验一下用户身份是否可以查看当前数据 尽量中间件校验
        // 删除数据 并且捕获异常
        try{
            // 删除数据 默认伪删除
            $res  = (new MarkeringMenuCategoryModel())->deleteInfo(['id'=>$id]);
        }catch (\Exception $e){
            return JsonUtils::returnDataErr($e->getMessage(),DATA_IS_WRONG);
        }
        if(!$res){
            return JsonUtils::returnDataErr('删除失败');
        } else {
            return JsonUtils::returnDataSuc('删除成功');
        }
    }

    

    /**
     * 更改数据状态 - 开启or禁用
     * @param int $id
     * @param int $status
     * @return array
     */
    public static function categoryStatus(int $id,int $status){
        // 更新数据 并且捕获异常
        try{
            $res  = (new MarkeringMenuCategoryModel())->updateInfo(['id'=>$id],['status'=>$status]);
        }catch (\Exception $e){
            return JsonUtils::returnDataErr($e->getMessage(),DATA_IS_WRONG);
        }
        if(!$res){
            return JsonUtils::returnDataErr('更新失败');
        } else {
            return JsonUtils::returnDataSuc('更新成功');
        }
    }

}
