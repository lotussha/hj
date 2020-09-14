<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/8/12 15:55
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
namespace app\apiadmin\logic\seckill;

use app\common\model\seckill\SeckillModel;
use sakuno\utils\JsonUtils;

/**
 * 营销管理 - 秒杀业务层
 * Class SeckillLogic
 * @package app\apiadmin\logic\seckill
 */
class SeckillLogic
{

    /**
     * TODO 校验当前秒杀活动是否存在订单
     * @param int $seckill_id
     * @return bool
     */
    protected static function seckillExistOrder(int $seckill_id){
        return false;
    }

    /**
     * 校验时间段是否存在活动
     * @param int|null $time_id
     * @return bool
     */
    static function seckillExistCheckCanDo(int $time_id = null){
        if(is_null($time_id)) return false;
        // 根据时间id获取当前时间段是否存在秒杀活动
        $where[] = ['time_id','=',$time_id];
        $where[] = ['is_delete','=',config('status.mysql.table_not_delete')];
        if((new SeckillModel())->statInfo($where)){
            return false;
        } else {
            return true;
        }
    }

    /**
     * 校验是不是平台
     */
    protected static function platformCheck(){
        return true;
    }

    /**
     * 校验当前数据是否有权限操作或查看
     * @param int|null $opt_source
     * @param int|null $operator
     * @return bool
     */
    protected static function authCheck(int $opt_source = null,int $operator = null){
        // TODO 暂定为写死的平台方数据
        $is_platform = true;
        // 判断是否是平台方 是则直接返回true 否则校验数据是否为self
        if($is_platform){
            return true;
        } else {
            // TODO 校验是否自己的数据 待完善
            if($opt_source && $operator){
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 新增秒杀数据
     * @param $data
     * @return \think\Response
     */
    public static function seckillAdd($data){
        // TODO 获取当前操作者身份和ID 待处理 暂写死 (待处理)
        $data['opt_source'] = 1; // 身份 1 admin平台 2 supplyer供应商 3 store商家 4 user团长
        $data['operator'] = 1; // 操作者ID
        // TODO 校验商品是否可以选择 (待完善)
        // 处理数据
        $data['start_time'] = strtotime($data['start_time']);
        $data['stop_time'] = strtotime($data['stop_time']);
        // 保存数据 并捕获异常
        try{
            $res = (new SeckillModel())->addInfo($data);
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
     * 查看秒杀数据
     * @param int|null $id
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function seckillRead(int $id = null){
        if(is_null($id)) return JsonUtils::fail('id不能为空',PARAM_IS_BLANK);
        $res = (new SeckillModel())->getSeckillDetail(['id'=>$id,'is_delete'=>config('status.mysql.table_not_delete')],'',['is_delete','delete_time']);
        if(empty($res)) return JsonUtils::fail('数据不存在',RESULE_DATA_NONE);
        // TODO 校验身份权限
        if(!self::authCheck($res['opt_source'],$res['operator'])) return JsonUtils::fail('暂无操作权限',PERMISSION_NO_ACCESS);
        // 处理数据
        $res['start_time'] = date('Y-m-d',$res['start_time']);
        $res['stop_time'] = date('Y-m-d',$res['stop_time']);
        $res['seckill_images'] = empty($res['seckill_images']) ? [] : explode(',',$res['seckill_images']);
        return JsonUtils::successful('获取成功',$res);
    }

    /**
     * 编辑秒杀数据
     * @param int $id
     * @param array $data
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function seckillEdit(int $id,array $data = []){
        if(is_null($id)) return JsonUtils::fail('id不能为空',PARAM_IS_BLANK);
        $info = (new SeckillModel())->getSeckillDetail(['id'=>$id,'is_delete'=>config('status.mysql.table_not_delete')],'',['is_delete','delete_time']);
        if(empty($info)) return JsonUtils::fail('数据不存在',RESULE_DATA_NONE);
        // TODO 校验身份权限
        if(!self::authCheck($info['opt_source'],$info['operator'])) return JsonUtils::fail('暂无操作权限',PERMISSION_NO_ACCESS);
        // TODO 校验活动当前是否存在订单存在则不能修改
        if(self::seckillExistOrder($id)) return JsonUtils::fail('当前活动存在订单，不可修改',SPECIFIED_QUESTIONED_USER_NOT_EXIST);

        // TODO 校验商品是否可以选择 (待完善)

        // 处理数据
        $data['start_time'] = strtotime($data['start_time']);
        $data['stop_time'] = strtotime($data['stop_time']);
        // 更新数据 并且捕获异常
        try{
            $res  = (new SeckillModel())->updateInfo(['id'=>$id],$data,['id']);
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
     * 删除秒杀数据
     * @param int $id
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function seckillDel(int $id){
        if(is_null($id)) return JsonUtils::fail('id不能为空',PARAM_IS_BLANK);
        $info = (new SeckillModel())->getSeckillDetail(['id'=>$id,'is_delete'=>config('status.mysql.table_not_delete')],'',['is_delete','delete_time']);
        if(empty($info)) return JsonUtils::fail('数据不存在',RESULE_DATA_NONE);
        // TODO 校验身份权限
        if(!self::authCheck($info['opt_source'],$info['operator'])) return JsonUtils::fail('暂无操作权限',PERMISSION_NO_ACCESS);
        // TODO 校验活动当前是否存在订单存在则不能修改
        if(self::seckillExistOrder($id)) return JsonUtils::fail('当前活动存在订单，不可操作',SPECIFIED_QUESTIONED_USER_NOT_EXIST);
        // 删除数据 并且捕获异常
        try{
            // 删除数据 默认伪删除
            $res  = (new SeckillModel())->deleteInfo(['id'=>$id]);
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
     * 更改秒杀数据状态 1启用 2禁用
     * @param int $id
     * @param int $status
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function seckillState(int $id,int $status){
        if(is_null($id)) return JsonUtils::fail('id不能为空',PARAM_IS_BLANK);
        $info = (new SeckillModel())->getSeckillDetail(['id'=>$id,'is_delete'=>config('status.mysql.table_not_delete')],'',['is_delete','delete_time']);
        if(empty($info)) return JsonUtils::fail('数据不存在',RESULE_DATA_NONE);
        // TODO 校验身份权限
        if(!self::authCheck($info['opt_source'],$info['operator'])) return JsonUtils::fail('暂无操作权限',PERMISSION_NO_ACCESS);
        // TODO 校验活动当前是否存在订单存在则不能修改
        if(self::seckillExistOrder($id)) return JsonUtils::fail('当前活动存在订单，不可操作',SPECIFIED_QUESTIONED_USER_NOT_EXIST);
        // 更新数据 并且捕获异常
        try{
            $res  = (new SeckillModel())->updateInfo(['id'=>$id],['status'=>$status]);
        }catch (\Exception $e){
            return JsonUtils::fail($e->getMessage(),DATA_IS_WRONG);
        }
        if(!$res){
            return JsonUtils::fail('更新失败');
        } else {
            return JsonUtils::successful('更新成功');
        }
    }

    public static function seckillLists(int $page = 1,int $page_size = 10,int $last_id = null){
        // 查询条件
        $where[] = ['is_delete','=',config('status.mysql.table_not_delete')];
        if ($page > 1){
            !empty($last_id) && $where[] = ['id','<',$last_id];
        }
        // TODO 考虑权限 (待完善)
        if(!self::authCheck(null,null,false)){
            $where[] = ['operator','=',1];
            $where[] = ['opt_source','=',1];
        }
        // 获取数据列表
    }

}
