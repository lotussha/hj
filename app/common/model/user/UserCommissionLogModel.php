<?php


namespace app\common\model\user;


use app\common\model\CommonModel;

//用户佣金明细
class UserCommissionLogModel extends CommonModel
{
    protected $name = 'user_commission_log';

    //可作为条件的字段
    protected $whereField = [
        'uid',
        'commission_status'
    ];

    //可字段搜索器 时间范围查询
    protected $timeField = [
        'create_time',
    ];

    /**
     * 收益用户
     * User: hao
     * Date: 2020-08-14
     * @return \think\model\relation\hasOne
     */
    public function Users()
    {
        return $this->hasOne('UserModel', 'id', 'uid')->field('id,nick_name,username');
    }


    /**
     *购买用户
     * User: hao
     * Date: 2020-08-14
     * @return \think\model\relation\hasOne
     */
    public function UsersBuy()
    {
        return $this->hasOne('UserModel', 'id', 'pid')->field('id,nick_name,username');
    }

    /**
     *购买商品
     * User: hao
     * Date: 2020-08-14
     * @return \think\model\relation\hasOne
     */
    public function Goods()
    {
        return $this->hasOne('app\common\model\GoodsModel', 'goods_id', 'goods_id')->field('goods_id,goods_name');
    }


    /**
     *购买商品
     * User: hao
     * Date: 2020-08-14
     * @return \think\model\relation\hasOne
     */
    public function Orders()
    {
        return $this->hasOne('app\common\model\order\OrderModel', 'order_sn', 'order_sn')->field('order_sn,order_status');
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
    public function getAllCommission($receive)
    {
        $receive['list_rows'] = isset($receive['list_rows']) ? $receive['list_rows'] : 10;  //多少条
        $receive['field'] = isset($receive['field']) ? $receive['field'] : true;//指定字段
        $receive['where'] = isset($receive['where']) ? $receive['where'] : [];//条件
        $data = $this
            ->with(['Users', 'UsersBuy', 'Goods', 'Orders'])
            ->field($receive['field'])
            ->scope('where', $receive)
            ->where($receive['where'])
            ->append(['type_text'])
            ->paginate($receive['list_rows']);
        foreach ($data as $k => $v) {
            $v['nick_name'] = $v->Users['nick_name'];
            unset($v['Users']);

            $v['buy_nick_name'] = $v->UsersBuy['nick_name'];
            $v['buy_username'] = $v->UsersBuy['username'];
            unset($v['UsersBuy']);

            $v['goods_name'] = $v->Goods['goods_name'];
            unset($v['Goods']);

            $v['order_status'] = $v->Orders['order_status'];
            unset($v['Orders']);
            $data[$k] = $v;
        }
        return $data->toArray();
    }

    public function getTypeTextAttr($value, $data)
    {
        $arr = [
            '101' => '管理员添加佣金',
            '102' => '用户下单待返佣金',
            '103' => '用户下单待返佣金',
            '104' => '用户提现失败退回金额',

            '201' => '用户退货扣除待返佣金',
            '202' => '用户完成扣除待返佣金',
            '203' => '用户提现佣金',
        ];
        return $arr[$data['type']] ?? '';
    }


}