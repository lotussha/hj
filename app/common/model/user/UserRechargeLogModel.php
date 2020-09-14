<?php


namespace app\common\model\user;


use app\common\model\CommonModel;
use think\db\Query;

//用户充值记录
class UserRechargeLogModel extends CommonModel
{

    protected $name='user_recharge_log';

    //可作为条件的字段
    protected $whereField = [
        'uid',
        'status'
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
     * Date: 2020/8/19
     */
    public function getAllRecharge($receive){
        $receive['list_rows'] = isset($receive['list_rows'])?$receive['list_rows']:10;  //多少条
        $receive['field'] = isset($receive['field'])?$receive['field']:'';//指定字段

        $data = $this->with(['Users'])
            ->field($receive['field'])
            ->scope('where', $receive)
            ->append(['status_text','type_text'])
            ->paginate($receive['list_rows']);

        foreach ($data as $k=>$v){
            $v['nick_name'] = $v->Users['nick_name'];
            unset($v['Users']);
            $data[$k] = $v;
        }
        return $data->toArray();
    }

    public function getStatusTextAttr($value, $data)
    {
        switch ($data['status']){
            case 1:
                $str = '成功';
                break;
            case 2:
                $str='失败';
                break;
        }
        return $str;
    }
    public function getTypeTextAttr($value, $data)
    {
        switch ($data['type']){
            case 1:
                $str = '微信小程序';
                break;
            case 2:
                $str='管理员充值';
                break;
        }
        return $str;
    }

}