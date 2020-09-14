<?php


namespace app\common\model\user;


use app\common\model\CommonModel;

//用户余额明细
class UserBalanceLogModel extends CommonModel
{
    protected $name = 'user_balance_log';


    //可字段搜索器 时间范围查询
    protected $timeField = [
        'create_time',
    ];

    //可作为条件的字段
    protected $whereField = [
        'uid',
        'type',
    ];



    /**
     * 添加余额明细
     * @param string  $uid  用户id
     * @param string   $type     1:充值  2：提现 3：购买商品  4:后台充值  5:退款返回  6：提现审核失败退回
     * @param string   $money     金额
     * @param string   $original_money     原来金额
     * @param string   $now_money     现在金额
     * @param string   $order_sn     关联订单号
     * @param string   $goods_id     关联商品
     * @return array
     * @author hao     2020.01.07
     */
    public function addBalance($uid,$type,$money,$original_money,$now_money,$order_sn='0'){
        switch ($type){
            case 2:
            case 3:
                $money = '-'.$money;
                break;
        }

        $data = array();
        $data['uid'] = $uid;
        $data['type'] = $type;
        $data['money'] = $money;
        $data['order_sn'] = $order_sn;
        $data['original_money'] =$original_money;
        $data['now_money'] = $now_money;
        $res =$this->addInfo($data);
        return $res;
    }


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
     * Date: 2020/8/28
     */
    public function getAllBalance($receive){
        $receive['list_rows'] = isset($receive['list_rows'])?$receive['list_rows']:10;  //多少条
        $receive['field'] = isset($receive['field'])?$receive['field']:'';//指定字段
        $receive['where'] = isset($receive['where'])?$receive['where']:[];//条件
        $data = $this->with(['Users'=>function($query){
            $query->field('nick_name');
        }])
            ->field($receive['field'])
            ->scope('where', $receive)
            ->where($receive['where'])
            ->append(['type_text'])
            ->paginate($receive['list_rows']);
        foreach ($data as $k=>$v){
            $v['nick_name'] = $v->Users['nick_name'];
            unset($v['Users']);
            $data[$k] = $v;
        }
        return $data->toArray();
    }

    public function getTypeTextAttr($value,$data)
    {
        $arr =[
            '1'=>'充值',
            '2'=>'提现',
            '3'=>'购买商品',
            '4'=>'管理员充值',
            '5'=>'退款返回',
        ];
        return $arr[$data['type']] ?? '';
    }



}