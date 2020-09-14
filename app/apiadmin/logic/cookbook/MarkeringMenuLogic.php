<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/8/12 11:25
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
use app\common\model\cookbook\MarkeringMenuModel;
use sakuno\utils\JsonUtils;
use app\common\model\GoodsModel;
use app\common\model\user\UserModel;
use app\apiadmin\model\AdminUsers;

/**
 * 菜谱
 * Class MarkeringMenuLogic
 * @package app\apiadmin\logic\cookbook
 */
class MarkeringMenuLogic
{

    /**
     * 校验是否存在权限
     * @param string $opt_source
     * @param int $operator
     * @param bool $is_check
     * @return bool
     */
    protected static function authCheck(string $opt_source = null,int $operator = null,bool $is_check = true){
        return true;
    }

    /**
     * 菜谱新增
     * @param $data
     * @return \think\Response
     */
    public static function menuAdd($data){

        //获取登录的真实token 存入相关日志
        $checkToken = checkToken($data['token']);
        $admin = (new AdminUsers())->where(['id'=>$checkToken['data']->admin_id])->find();
        
        // TOKEN 获取当前操作者身份和ID 
        $data['opt_source'] = $admin['identity']; // 身份：1:平台2:供货商3:门店4:团长5：仓库
        $data['operator']   = $admin['id'];       // 操作者ID
        try{
            $res = (new MarkeringMenuModel())->addInfo($data);
        }catch (\Exception $e){
            return JsonUtils::fail($e->getMessage(),DATA_IS_WRONG);
        }
        if(!$res){
            return JsonUtils::fail('保存失败');
        } else {
            return JsonUtils::successful('保存成功');
        }
    }

    /**
     * 菜谱查看
     * @param int $id
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function menuRead(int $id){
        // 获取数据
        $res = (new MarkeringMenuModel())->findInfo(['id'=>$id,'is_delete'=>config('status.mysql.table_not_delete')],'',['is_delete','delete_time']);

        if(empty($res)) return JsonUtils::fail('数据不存在',RESULE_DATA_NONE);
        // TODO 这里有需要的话 校验一下用户身份是否可以查看当前数据 尽量中间件校验 (待处理)
        if(!self::authCheck($res['opt_source'],$res['operator'])) return JsonUtils::fail('暂无操作权限',PERMISSION_NO_ACCESS);
        // 获取分类名称
        $res['category_title'] = (new MarkeringMenuCategoryModel())->getValues(['id'=>$res['cate_id']],'category_title');
        //获取商品图片
        $res['goods_img'] = (new GoodsModel())->getValues(['goods_id'=>$res['goods_id']],'original_img');
        //获取关联会员信息
        $res['user_url']  = (new UserModel())->getValues(['id'=>$res['user_id']],'avatar_url');
        //获取主料商品图片
        $res['goods_img1'] = (new GoodsModel())->where('goods_id', 'in', $res['main_goods_id'])->column('original_img');
        //获取辅料商品图片
        $res['goods_img2'] = (new GoodsModel())->where('goods_id', 'in', $res['auxiliary_goods_id'])->column('original_img');
        // 返回数据
        return JsonUtils::successful('获取成功',$res);
    }

    /**
     * 菜谱编辑
     * @param int $id
     * @param $data
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function menuEdit(int $id,$data){
        $info = (new MarkeringMenuModel())->findInfo(['id'=>$id,'is_delete'=>config('status.mysql.table_not_delete')]);
        if(empty($info)) return JsonUtils::fail('数据不存在',RESULE_DATA_NONE);
        // TODO 这里有需要的话 最好校验一下用户身份是否可以查看当前数据 尽量中间件校验
        if(!self::authCheck($info['opt_source'],$info['operator'])) return JsonUtils::fail('暂无操作权限',PERMISSION_NO_ACCESS);
        // 更新数据 并且捕获异常
        try{
            $res  = (new MarkeringMenuModel())->updateInfo(['id'=>$id],$data,['id']);
        }catch (\Exception $e){
            return JsonUtils::fail($e->getMessage(),DATA_IS_WRONG);
        }
        if(!$res){
            return JsonUtils::fail('保存失败');
        } else {
            return JsonUtils::successful('保存成功');
        }
    }

    /**
     * 菜谱删除
     * @param int $id
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function menuDel(int $id){
        $info = (new MarkeringMenuModel())->findInfo(['id'=>$id,'is_delete'=>config('status.mysql.table_not_delete')]);
        if(empty($info)) return JsonUtils::fail('数据不存在',RESULE_DATA_NONE);
        // TODO 这里有需要的话 最好校验一下用户身份是否可以查看当前数据 尽量中间件校验
        if(!self::authCheck($info['opt_source'],$info['operator'])) return JsonUtils::fail('暂无操作权限',PERMISSION_NO_ACCESS);
        // 删除数据 并且捕获异常
        try{
            // 删除数据 默认伪删除
            $res  = (new MarkeringMenuModel())->deleteInfo(['id'=>$id]);
        }catch (\Exception $e){
            return JsonUtils::fail($e->getMessage(),DATA_IS_WRONG);
        }
        if(!$res){
            return JsonUtils::fail('删除失败');
        } else {
            return JsonUtils::successful('删除成功');
        }
    }

    /**
     * 菜谱开启 or 禁用
     * @param int $id
     * @param int $status
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function menuState(int $id,int $status){
        $info = (new MarkeringMenuModel())->findInfo(['id'=>$id,'is_delete'=>config('status.mysql.table_not_delete')]);
        if(empty($info)) return JsonUtils::fail('数据不存在',RESULE_DATA_NONE);
        // TODO 这里有需要的话 最好校验一下用户身份是否可以查看当前数据 尽量中间件校验
        if(!self::authCheck($info['opt_source'],$info['operator'])) return JsonUtils::fail('暂无操作权限',PERMISSION_NO_ACCESS);
        // 更新数据 并且捕获异常
        try{
            $res  = (new MarkeringMenuModel())->updateInfo(['id'=>$id],['status'=>$status]);
        }catch (\Exception $e){
            return JsonUtils::fail($e->getMessage(),DATA_IS_WRONG);
        }
        if(!$res){
            return JsonUtils::fail('更新失败');
        } else {
            return JsonUtils::successful('更新成功');
        }
    }

    /**
     * 获取菜谱列表(分页)
     * @param int $page
     * @param int $page_size
     * @param int|null $last_id
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function menuLists(int $page = 1,int $page_size = 10,int $last_id = null){
        // 查询条件
        $where[] = ['is_delete','=',config('status.mysql.table_not_delete')];
        if ($page > 1){
            !empty($last_id) && $where[] = ['id','<',$last_id];
        }
        // TODO 考虑权限 (待完善)
        if(!self::authCheck(null,null,false)){
            $where[] = ['operator','=',1];
        }
        // 获取数据列表
        $model = new MarkeringMenuModel();
        $list = $model->getMarkeringMenuListsPage($where,$page_size,'',['id'=>'desc'],['is_delete','delete_time']);
        foreach ($list as $key => $value) {
            //获取主料商品名称
            $goods_name1 = (new GoodsModel())->where('goods_id', 'in', $value['main_goods_id'])->column('goods_name');
            //获取辅料商品名称
            $goods_name2 = (new GoodsModel())->where('goods_id', 'in', $value['auxiliary_goods_id'])->column('goods_name');
            $list[$key]['goods_name1'] = implode(",",$goods_name1);  
            $list[$key]['goods_name2'] = implode(",",$goods_name2);  
        }
        $total = $model->statInfo($where);
        $page_total = ceil($total/$page_size);
        $res = ['total'=>$total,'page'=>$page,'page_total'=>$page_total,'list'=>$list,'last_id'=>array_pop($list)['id']];
        return JsonUtils::successful('获取成功',$res);
    }

    /**
     * 获取所有商品列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function goodsList(){
        $res = (new GoodsModel())->getList(['is_del'=>0],'goods_id,goods_name,original_img');
        return $res;
    }

}
