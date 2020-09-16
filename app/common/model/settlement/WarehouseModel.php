<?php


namespace app\common\model\settlement;

use app\common\model\CommonModel;
use think\Model;

//仓库
class WarehouseModel extends CommonModel
{
    protected $name = 'warehouse';

    /**
     * 获取列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * User: hao
     * Date: 2020/8/5
     */
    public function getAllWarehouse($receive){
        $receive['list_rows'] = isset($receive['list_rows'])?$receive['list_rows']:10;  //多少条
        $receive['field'] = isset($receive['field'])?$receive['field']:'';//指定字段

        $data = $this
            ->field($receive['field'])
            ->scope('where', $receive)
            ->paginate($receive['list_rows']);
        return $data->toArray();
    }
    /**
     * 获取详情
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * User: hao
     * Date: 2020/8/5
     */
    public function getInfoWarehouse($where,$field){
        $data = $this
            ->field($field)
            ->where($where)
            ->find();

        if ($data){
            $data = $data->toArray();
        }else{
            $data = array();
        }
        return $data;
    }
}