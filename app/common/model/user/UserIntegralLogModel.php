<?php


namespace app\common\model\user;


use app\common\model\CommonModel;
//用户积分来源表
class UserIntegralLogModel extends CommonModel
{
    protected $name='user_integral_log';


    //可作为条件的字段
    protected $whereField = [
        'uid',
        'integral_type',
        'type',
    ];

    //可字段搜索器 时间范围查询
    protected $timeField = [
        'create_time',
    ];

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
    public function getAllIntegral($receive){

        $receive['list_rows'] = isset($receive['list_rows'])?$receive['list_rows']:10;  //多少条
        $receive['field'] = isset($receive['field'])?$receive['field']:'';//指定字段
        $receive['where'] = isset($receive['where'])?$receive['where']:[];//条件
        $data = $this->with(['Users'=>function($query){
            $query->field('nick_name');
        }])
            ->field($receive['field'])
            ->scope('where', $receive)
            ->where($receive['where'])
            ->append(['type_text','integral_type_text'])
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
            '101'=>'订单下单获取冻结积分',
            '102'=>'订单完成减去冻结积分',
            '103'=>'订单完成加入可使用积分',
            '104'=>'退款去除冻结积分',
            '201'=>'管理员修改积分',
            '301'=>'签到获取积分',
        ];
        return $arr[$data['type']] ?? '';
    }

    public function getIntegralTypeTextAttr($value,$data)
    {
        $arr =[
            '1'=>'冻结积分',
            '2'=>'增加使用积分',
            '3'=>'消费积分',
        ];
        return $arr[$data['integral_type']] ?? '';
    }


}