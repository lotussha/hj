<?php


namespace app\apiadmin\controller\settlement;

use app\apiadmin\controller\Base;
use app\common\model\settlement\SettlementModel;
use app\common\validate\settlement\SettlementValidate;
use app\common\logic\settlement\SettlementLogic;
use sakuno\utils\JsonUtils;

//入驻管理
class Settlement extends Base
{
    /**
     * 入驻列表
     * User: hao
     * Date: 2020/8/8
     */
    public function index(SettlementModel $settlementModel)
    {
        $data =$this->param;
        $data['list_rows'] = $this->admin['list_rows'];
        $data['field'] ='id,uid,identity,username,nickname,contacts,phone,examine_is,address,business_license,province,city,county,twon';
        $res  = $settlementModel->getAllSettlement($data);
        return JsonUtils::successful('获取成功', $res);


    }

    /**
     * 入驻详情
     * User: hao
     * Date: 2020/8/8
     */
    public function info(SettlementModel $settlementModel, SettlementValidate $validate)
    {
        $arr = array_return();
        //检验
        $validate_result = $validate->scene('info')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(), '00000');
        }
        $field='id,uid,identity,username,nickname,contacts,phone,examine_is,address,business_license,province,city,county,twon';
        $lists = $settlementModel->getInfoSettlement(['id' => $this->param['id']],$field);
        return JsonUtils::successful('获取成功', $lists);
    }

    /**
     * 入驻添加
     * User: hao
     * Date: 2020/8/8
     */
    public function add(SettlementLogic $logic)
    {
        $data = $this->param;
        //检测
        $validate = new SettlementValidate();
        $validate_result = $validate->scene('admin_add')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(), '00000');
        }

        $rs = $logic->AddHandle($data);
        return $rs;
    }

    /**
     * 入驻修改
     * User: hao
     * Date: 2020/8/8
     */
    public function edit(SettlementLogic $logic)
    {
        $data = $this->param;

        //检测
        $validate = new SettlementValidate();
        $validate_result = $validate->scene('edit')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(), '00000');
        }

        $rs = $logic->EditHandle($data);
        return $rs;


    }

    /**
     * 身份禁用
     * User: hao
     * Date: 2020/8/8
     */
    public function status(SettlementModel $settlementModel)
    {
        $data = $this->param;
        //检测
        $validate = new SettlementValidate();
        $validate_result = $validate->scene('status')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(), '00000');
        }
        $arr_data = array();
        $arr_data['status'] = $data['status'];
        if ($data['status'] == 2) {
            $arr_data['disable_time'] = time();
        }
        $arr = $settlementModel->updateInfo(['id' => $data['id']],$arr_data);
        if ($arr) {
            return JsonUtils::successful('操作成功');
        } else {
            return JsonUtils::fail('操作失败', '00000');
        }
    }

    /**
     * 入驻审核
     * User: hao
     * Date: 2020/8/8
     */
    public function examine()
    {
        $data = $this->param;
        //检测
        $validate = new SettlementValidate();
        $validate_result = $validate->scene('examine')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(), '00000');
        }
        $logic = new SettlementLogic();
        $rs = $logic->ExamineHandle($data);
        return $rs;
    }


}