<?php


namespace app\common\model\user;


use app\common\model\CommonModel;

class UserMoneyLogModel extends CommonModel
{
    protected $name = 'user_money_log';
    /**
     * 用户
     * User: hao
     * Date: 2020-08-14
     * @return \think\model\relation\hasOne
     */
    public function Users(){
        return $this->hasOne('UserModel', 'id', 'uid')->field('id,nick_name,username');
    }

    /**
     * 获取列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * User: hao
     * Date: 2020/8/14
     */
    public function getAllMoney($receive){
        $receive['page'] = isset($receive['page'])?$receive['page']:1;//页数
        $receive['list_row'] = isset($receive['list_row'])?$receive['list_row']:10;  //多少条
        $receive['where'] = isset($receive['where'])?$receive['where']:[];//条件
        $receive['field'] = isset($receive['field'])?$receive['field']:'';//指定字段
        $receive['hidden'] = isset($receive['hidden'])?$receive['hidden']:['']; //排除字段
        $receive['order'] = isset($receive['order'])?$receive['order']:'id desc'; //排序


        $data = $this->with(['Users'=>function($query){
            $query->field('nick_name');
        }])
            ->field($receive['field'])
            ->hidden($receive['hidden'])
            ->where($receive['where'])
            ->page($receive['page'], $receive['list_row'])
            ->order($receive['order'])
            ->select();
        foreach ($data as $k=>$v){
            $v['nick_name'] = $v->Users['nick_name'];
            unset($v['Users']);
            $data[$k] = $v;

        }
        return arrString($data->toArray());
    }

}