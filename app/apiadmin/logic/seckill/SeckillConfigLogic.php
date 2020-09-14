<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/8/11 15:55
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

use app\common\model\seckill\SeckillConfigModel;
use sakuno\utils\JsonUtils;

/**
 * 秒杀时间段配置
 * Class SeckillConfigLogic
 * @package app\common\logic\seckill
 */
class SeckillConfigLogic
{

    /**
     * 新增时间段
     * @param $sort
     * @param $hours
     * @param $time_start
     * @return \think\Response
     */
    public static function configAdd($sort,$hours,$time_start){
        // 处理数据
        (int)$data['sort'] = !empty($sort) ? $sort : 1;
        (int)$data['hours'] = !empty($hours) ? $hours : 2;
        (int)$data['time_start'] = !empty($time_start) ? $time_start : 0;
        // 判断时间段
        if($time_start < 0 || $time_start > 24){
            return JsonUtils::fail('开始时间必须0~24整数');
        }
        // 保存数据 并捕获异常
        try{
            $res = (new SeckillConfigModel())->addInfo($data);
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
     * 根据id获取秒杀时间段数据
     * @param $id
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function configFind($id){
        if(empty($id) || !is_numeric($id)) return JsonUtils::fail('参数错误',PARAM_IS_INVALID);
        $res = (new SeckillConfigModel())->findInfo(['id'=>$id,'is_delete'=>config('status.mysql.table_not_delete')],'',['is_delete','delete_time']);
        if(empty($res)) return JsonUtils::fail('数据不存在',RESULE_DATA_NONE);
        return JsonUtils::successful('获取成功',$res);
    }

    /**
     * 更新秒杀时间段数据
     * @param $id
     * @param $sort
     * @param $hours
     * @param $time_start
     * @return \think\Response
     */
    public static function configEdit($id,$sort,$hours,$time_start){
        if(empty($id) || !is_numeric($id)) return JsonUtils::fail('参数错误',PARAM_IS_INVALID);
        // TODO 预留校验是否存在该时间段的秒杀活动
        if(!SeckillLogic::seckillExistCheckCanDo($id)) return JsonUtils::fail('该时间段存在秒杀活动，请删除后活动后再进行操作',SPECIFIED_QUESTIONED_USER_NOT_EXIST);
        // 处理数据
        (int)$data['sort'] = $sort ?? 1;
        (int)$data['hours'] = $hours ?? 2;
        (int)$data['time_start'] = $time_start ?? 0;
        // 判断时间段
        if($time_start < 0 || $time_start > 24){
            return JsonUtils::fail('开始时间必须0~24整数');
        }
        // 保存数据 并捕获异常
        try{
            $res = (new SeckillConfigModel())->updateInfo(['id'=>$id],$data);
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
     * 删除秒杀时间段数据
     * @param $id
     * @return \think\Response
     */
    public static function configDel($id){
        if(empty($id) || !is_numeric($id)) return JsonUtils::fail('参数错误',PARAM_IS_INVALID);
        // TODO 预留校验是否存在该时间段的秒杀活动
        if(!SeckillLogic::seckillExistCheckCanDo($id)) return JsonUtils::fail('该时间段存在秒杀活动，请删除后活动后再进行操作',SPECIFIED_QUESTIONED_USER_NOT_EXIST);
        // 删除数据 并且捕获异常
        try{
            // 删除数据 默认伪删除
            $res  = (new SeckillConfigModel())->deleteInfo(['id'=>$id]);
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
     * 更改数据状态 - 开启or禁用
     * @param int $id
     * @param int $status
     * @return \think\Response
     */
    public static function categoryStatus(int $id,int $status){
        if(empty($id)||!is_numeric($id)||empty($status)||!in_array($status,[1,2])) return JsonUtils::fail('参数错误',PARAM_IS_INVALID);
        // TODO 预留校验是否存在该时间段的秒杀活动
        if(!SeckillLogic::seckillExistCheckCanDo($id)) return JsonUtils::fail('该时间段存在秒杀活动，请删除后活动后再进行操作',SPECIFIED_QUESTIONED_USER_NOT_EXIST);
        // 更新数据 并且捕获异常
        try{
            $res  = (new SeckillConfigModel())->updateInfo(['id'=>$id],['status'=>$status]);
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
     * 获取数据列表 - all
     * @param int $is_title
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function categoryLists(int $is_title = 0){
        $where[] = ['is_delete','=',config('status.mysql.table_not_delete')];
        $res = (new SeckillConfigModel())->getList($where,'',['sort'=>'desc','id'=>'desc'],['is_delete','delete_time']);
        // 判断是否处理标题
        if($is_title){
            foreach ($res as &$v){
                $v['title'] = $v['time_start'].'点开始，持续'.$v['hours'].'小时';
            }
        }
        return JsonUtils::successful('获取成功',$res);
    }

}
