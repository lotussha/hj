<?php

namespace app\apiadmin\controller\settlement;

//仓库
use app\apiadmin\controller\Base;
use sakuno\utils\JsonUtils;
use app\common\model\settlement\WarehouseModel;
use app\common\validate\settlement\WarehouseValidate;
use app\common\logic\settlement\WarehouseLogic;

class Warehouse extends Base
{
    /**
     * 仓库列表
     * User: hao
     * Date: 2020/8/8
     */
    public function index(WarehouseModel $warehouseModel){
        $data =$this->param;
        $data['list_rows'] = $this->admin['list_rows'];
        $data['field'] = 'nickname,username,address,contacts,phone,status,full_package,freight_id';
        $res = $warehouseModel->getAllWarehouse($data);
        return JsonUtils::successful('获取成功',$res);
    }

    /**
     * 仓库详情
     * User: hao
     * Date: 2020/8/8
     */
    public function info(WarehouseModel $warehouseModel,WarehouseValidate $validate){
        //检验
        $validate_result = $validate->scene('info')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),'00000');
        }
        $field = 'nickname,username,address,contacts,phone,status,full_package,freight_id';
        //路由
        $lists = $warehouseModel->getInfoWarehouse(['id'=>$this->param['id']],$field);

        return JsonUtils::successful('获取成功',$lists);
    }

    /**
     * 仓库添加
     * User: hao
     * Date: 2020/8/8
     */
    public function add(WarehouseModel $warehouseModel,WarehouseValidate $validate){
        $data = $this->param;
        $validate_result = $validate->scene('add')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),'00000');
        }
        $Logic = new WarehouseLogic();

        $data = $Logic->Handle($data);
        if (!$data){
            return JsonUtils::fail('已有相同的账号','00000');
        }

        //模型
        $arr =$warehouseModel->addInfo($data);

        if ($arr){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败','00000');
        }
    }

    /**
     * 仓库修改
     * User: hao
     * Date: 2020/8/8
     */
    public function edit(WarehouseModel $warehouseModel,WarehouseValidate $validate){
        $data = $this->param;
        $validate_result = $validate->scene('edit')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),'00000');
        }
        $Logic = new WarehouseLogic();

        $data = $Logic->Handle($data,'edit');
        if (!$data){
            return JsonUtils::fail('已有相同的账号','00000');
        }

        //模型
        $arr =$warehouseModel->updateInfo(['id'=>$this->param['id']],$data);

        if ($arr){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败','00000');
        }
    }

    /**
     * 仓库禁用/启用
     * User: hao
     * Date: 2020/8/8
     */
    public function status(WarehouseModel $warehouseModel,WarehouseValidate $validate){
        $data = $this->param;
        $validate_result = $validate->scene('status')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),'00000');
        }
        $arr_data = array();
        if ($data['status']==2){
            $arr_data['disable_time'] = time();
        }
        $arr_data['status'] = $data['status'];

        //模型
        $arr =$warehouseModel->saveInfo($arr_data,['id'=>$data['id']]);

        if ($arr){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败','00000');
        }
    }


}