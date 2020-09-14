<?php


namespace app\common\logic\user;

//用户地址
use app\common\model\RegionModel;
use app\common\model\user\UserAddressModel;
use sakuno\utils\JsonUtils;

class UserAddressLogic
{
    /**
     * 用户地址
     * User: hao 2020-8-21
     */
    public function lists($receive){

        $model = new UserAddressModel();
        $regionModel = new RegionModel();
        $where = array();
        $where[] = ['user_id', '=', $receive['user_id']];
        $where[] = ['is_delete', '<>', 1];
        $field = 'address_id,consignee,province,city,county,twon,address,mobile,is_default';

        $lists = $model->getList($where, $field, 'is_default desc,address_id desc');
        foreach ($lists as $key=>$value){
            $list_id = array();
            array_push($list_id,$value['province'],$value['city'],$value['county'],$value['twon']);
            $list_id = implode(',',$list_id);

            $region = $regionModel->where('id IN ('.$list_id.')')->column('short_name','id');
            $value['province_name'] = $region[$value['province']];
            $value['city_name'] = $region[$value['city']];
            $value['county_name'] = $region[$value['county']];
            $value['twon_name'] = $region[$value['twon']];
            $lists[$key] = $value;
        }
        return JsonUtils::successful('操作成功', ['list'=>$lists]);
    }

    /**
     * 用户地址详情
     * User: hao 2020-8-21
     */
    public function info($receive){
        $model = new UserAddressModel();
        $regionModel = new RegionModel();
        $where = array();
        $where[] = ['user_id', '=', $receive['user_id']];
        $where[] = ['address_id', '=', $receive['address_id']];
        $where[] = ['is_delete', '<>', 1];
        $hidden = ['longitude','latitude','create_time','update_time','delete_time','is_delete'];
        $lists = $model->findInfo($where, '*', $hidden);

        $list_id = array();
        array_push($list_id,$lists['province'],$lists['city'],$lists['county'],$lists['twon']);
        $list_id = implode(',',$list_id);
        $region = $regionModel->where('id IN ('.$list_id.')')->column('short_name','id');
        $lists['province_name'] = $region[$lists['province']];
        $lists['city_name'] = $region[$lists['city']];
        $lists['county_name'] = $region[$lists['county']];
        $lists['twon_name'] = $region[$lists['twon']];

        return JsonUtils::successful('操作成功', $lists);
    }


}