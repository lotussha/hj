<?php


namespace app\common\model\user;


use app\common\model\CommonModel;

//提现
class UserCashModel extends CommonModel
{
    protected $name = 'user_cash';

    //可作为条件的字段
    protected $whereField = [
        'uid',
        'examine_is',
        'type',
        'cash_mode',
    ];

    //可字段搜索器 时间范围查询
    protected $timeField = [
        'create_time',
    ];

    /**
     * 用户
     * User: hao
     * Date: 2020-08-10
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
     * Date: 2020/8/20
     */
    public function getAllCash($receive){
        $receive['list_rows'] = isset($receive['list_rows'])?$receive['list_rows']:10;  //多少条
        $receive['field'] = isset($receive['field'])?$receive['field']:true;//指定字段

        $data = $this->with(['Users'])
            ->field($receive['field'])
            ->scope('where', $receive)
            ->append(['text_examine_is'])
            ->paginate($receive['list_rows']);

        foreach ($data as $k=>$v){
            $v['nick_name'] = $v->Users['nick_name'];
            unset($v['Users']);
            $data[$k] = $v;
        }

        return $data->toArray();
    }


    public function getTextExamineIsAttr($value, $data){

        switch ($data['examine_is']){
            case 1:
                $str =  '审核通过';
                break;
            case 2:
                $str =  '审核不通过';
                break;
            case 3:
                $str = '审核中';
                break;
        }
        return $str;
    }


}