<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/8/10 11:23
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
namespace app\apiadmin\logic\log;

use app\common\model\log\GoodsLogModel;
use app\common\validate\GoodsLogValidate;
use sakuno\utils\JsonUtils;

/**
 * 商品日志
 * Class GoodsLogLogic
 * @package app\common\logic\log
 */
class GoodsLogLogic
{

    /**
     * 商品日志新增
     * @param int|null $goods_id 商品id
     * @param string $opt_source 操作来源 具体可查看 ./config/log_params.php
     * @param int|null $operator 操作者id
     * @param string $do 操作类型 具体可查看 ./config/log_params.php
     * @param string $goods_title 商品名称 (非必须 尽量传入)
     * @param string $desc 操作备注 (非必须 比如审核商品失败自定义备注)
     * @return array
     */
    public static function goodsLogAdd(int $goods_id = null,string $opt_source = '',int $operator = null, string $do = '',string $goods_title = '',string $desc = ''){
        $data = ['goods_id'=>$goods_id,'opt_source'=>$opt_source,'operator'=>$operator,'do'=>$do];
        // 校验数据合法性
        $validate = new GoodsLogValidate();
        if(!$validate->check($data)) {
            return JsonUtils::returnDataErr($validate->getError(),PARAM_IS_INVALID);
        }
        // 判断是否传入商品名称 没有则从数据库读取  (由于模型继承关系 这里可优化!!!!)
        $goods_title ?? model('GoodsModel')->where('id',$goods_id)->column('goods_name','id');
        // 获取操作类型
        $do_type = array_key_exists($do,config('log_params.goods_log.do_type')) ? config('log_params.goods_log.do_type')[$do] : '未知类型：';
        // 拼接操作日志
        $data['log_info'] = $do_type.$goods_title;
        !empty($desc) && $data['log_info'] .= "，备注:".$desc;
        unset($data['do']);
        // TODO 后期需要考虑权限

        // 实行新增日志 并捕获异常
        try{
            $res = (new GoodsLogModel())->goodsLogAdd($data);
        }catch (\Exception $e){
            return JsonUtils::returnDataErr($e->getMessage(),DATA_IS_WRONG);
        }
        if($res){
            return JsonUtils::returnDataSuc('日志记录成功');
        } else {
            return JsonUtils::returnDataErr('日志记录失败',DATA_IS_WRONG);
        }
    }

    /**
     * 商品日志删除
     * @param int|null $log_id 日志id
     * @param bool $is_true 是否真删除 0否(伪删除) 1是
     * @return array
     */
    public static function goodsLogDelete(int $log_id = null, bool $is_true = true){
        if(empty($log_id) || !is_numeric($log_id)){
            return JsonUtils::returnDataErr('参数异常',PARAM_IS_INVALID);
        }
        // TODO 后期需要考虑权限

        // 删除数据并且捕获异常
        try{
            $res = (new GoodsLogModel())->goodsLogDeleteById($log_id,$is_true);
        }catch (\Exception $e){
            return JsonUtils::returnDataErr($e->getMessage(),DATA_IS_WRONG);
        }
        if($res){
            return JsonUtils::returnDataSuc('日志删除成功');
        } else {
            return JsonUtils::returnDataErr('日志删除失败',DATA_IS_WRONG);
        }
    }

    /**
     * 根据商品id获取商品日志
     * @param int|null $goods_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function goodsLogListByGoodsId(int $goods_id = null){
        if(empty($goods_id) || !is_numeric($goods_id)){
            return JsonUtils::returnDataErr('参数异常',PARAM_IS_INVALID);
        }
        // TODO 后期需要考虑权限

        // 根据商品id获取日志列表
        $res = (new GoodsLogModel())->getList(['goods_id'=>$goods_id,'is_delete'=>0],'',['id'=>'desc'],['is_delete','delete_time']);
        // 处理数据
        if(!empty($res)){
            $source = config('log_params.goods_log.opt_source');
            foreach ($res as &$v){
                $v['source'] = $source[$v['opt_source']] ?? '未知来源';
            }
        }
        $total = count($res);
        $data = ['list'=>$res,'total'=>$total];
        return JsonUtils::returnDataSuc('获取成功',SUCCESS,$data);
    }

    /**
     * 根据商品id获取商品日志(分页)
     * @param int|null $goods_id
     * @param int|null $page
     * @param int|null $page_size
     * @param int|null $last_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function goodsLogListPageByGoodsId(int $goods_id = null,int $page = null,int $page_size = null,int $last_id = null){
        if(empty($goods_id) || !is_numeric($goods_id) || empty(($page))){
            return JsonUtils::returnDataErr('参数异常',PARAM_IS_INVALID);
        }
        // 查询条件
        $where[] = ['goods_id','=',$goods_id];
        $where[] = ['is_delete','=',0];
        if ($page > 1){
            !empty($last_id) && $where[] = ['id','>',$last_id];
        }
        // TODO 后期需要考虑权限

        // 获取数据列表 优化
        // $res = (new GoodsLogModel())->getListPage(['goods_id'=>$goods_id,'is_delete'=>0],$page,$page_size);
        $res = (new GoodsLogModel())->getLimitData($where,$page_size,'',['id'=>'desc'],['is_delete','delete_time']);
        // 处理数据
        if(!empty($res)){
            $source = config('log_params.goods_log.opt_source');
            foreach ($res as &$v){
                $v['source'] = isset($source[$v['opt_source']]) ?? '未知来源';
            }
        }
        // 获取列表总量
        $total = (new GoodsLogModel())->statInfo($where);
        $page_total = ceil($total/$page_size);
        $data = ['list'=>$res,'total'=>$total,'page'=>$page,'page_total'=>$page_total,'last_id'=>array_pop($res)['id']];
        return JsonUtils::returnDataSuc('获取成功',SUCCESS,$data);
    }

    /**
     * 获取商品日志(分页)
     * @param int|null $page
     * @param int|null $page_size
     * @param int|null $last_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function goodsLogListPage(int $page = null,int $page_size = null,int $last_id = null){
        if(empty(($page))){
            return JsonUtils::returnDataErr('参数异常',PARAM_IS_INVALID);
        }
        // 查询条件
        $where[] = ['is_delete','=',config('status.mysql.table_not_delete')];
        if ($page > 1){
            !empty($last_id) && $where[] = ['id','<',$last_id];
        }
        // TODO 后期需要考虑权限

        // 获取数据列表 优化
        $res = (new GoodsLogModel())->getLimitData($where,$page_size,'',['id'=>'desc'],['is_delete','delete_time']);
        // 处理数据
        if(!empty($res)){
            $source = config('log_params.goods_log.opt_source');
            foreach ($res as &$v){
                $v['source'] = isset($source[$v['opt_source']]) ?? '未知来源';
            }
        }
        // 获取列表总量
        $total = (new GoodsLogModel())->statInfo($where);
        $page_total = ceil($total/$page_size);
        $data = ['list'=>$res,'total'=>$total,'page'=>$page,'page_total'=>$page_total,'last_id'=>array_pop($res)['id']];
        return JsonUtils::returnDataSuc('获取成功',SUCCESS,$data);
    }


}
