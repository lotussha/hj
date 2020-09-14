<?php


namespace app\common\logic\config;

//充值
use app\common\model\config\RechargeOptionModel;

class RechargeOptionLogic
{
    /**
     * 充值判断合法金额
     * User: hao
     * Date: 2020/8/19
     */
    public function handle($receive,$act = ''){
        $model = new RechargeOptionModel();
        if ($receive['min_money']>$receive['max_money']){
            return ['data_code'=>false,'data_msg'=>'最小充值金额不能大于最大充值金额'];
        }
        if ($act=='edit'){
            $where = '(min_money <= '.$receive['min_money'].' and max_money >= '.$receive['min_money'].' and is_delete <> 1 and id <> '.$receive['id'].') or (min_money <= '.$receive['max_money'].' and max_money >= '.$receive['max_money'].' and is_delete <> 1 and id <> '.$receive['id'].')';
        }else{
            $where ='(min_money <= '.$receive['min_money'].' and max_money >= '.$receive['min_money'].' and is_delete <> 1) or (min_money <= '.$receive['max_money'].' and max_money >= '.$receive['max_money'].' and is_delete <> 1)';
        }
        $rs = $model->where($where)->value('id');
        if ($rs){
            return ['data_code'=>false,'data_msg'=>'最小金额或者最大金额已存在范围里'];
        }
        return $receive;
    }
}