<?php


namespace app\common\logic\user;

//用户等级
use app\common\model\user\UserGradeModel;
use sakuno\utils\JsonUtils;

class UserGradeLogic
{
    /**
     * 添加用户等级
     * User: hao
     * Date: 2020/8/17
     */
    public function addGrade($receive){

        //股东必填参数
        if (isset($receive['is_shareholder']) &&$receive['is_shareholder']==1){
            if (!isset($receive['bonus'])){
                return JsonUtils::fail('分红必填', PARAM_IS_INVALID);
            }
        }else{
        //普通等级必填参数
            if (!isset($receive['full_money'])){
                return JsonUtils::fail('满足升级产品金额升级必填', PARAM_IS_INVALID);
            }
            if (!isset($receive['share_num'])){
                return JsonUtils::fail('满足直推人数升级必填', PARAM_IS_INVALID);
            }
        }

        $userGradeModel = new UserGradeModel();
        $res = $userGradeModel->addInfo($receive);
        if ($res){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败');
        }

    }

    /**
     * 修改用户等级
     * User: hao
     * Date: 2020/8/17
     */
    public function editGrade($receive){
        //股东必填参数
        if (isset($receive['is_shareholder']) &&$receive['is_shareholder']==1){
            if (!isset($receive['bonus'])){
                return JsonUtils::fail('分红必填', PARAM_IS_INVALID);
            }
        }else{
            //普通等级必填参数
            if (!isset($receive['full_money'])){
                return JsonUtils::fail('满足升级产品金额升级必填', PARAM_IS_INVALID);
            }
            if (!isset($receive['share_num'])){
                return JsonUtils::fail('满足直推人数升级必填', PARAM_IS_INVALID);
            }
        }

        if ($receive['status']==2){
            $receive['disable_ime'] = time();
        }
        $userGradeModel = new UserGradeModel();
        $res = $userGradeModel->updateInfo(['id'=>$receive['id']],$receive);
        if ($res){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败');
        }
    }

    /**
     * 修改用户等级启用、禁用
     * User: hao
     * Date: 2020/8/17
     */
    public function statusGrade($receive){
        if ($receive['status']==2){
            $receive['disable_ime'] = time();
        }

        $userGradeModel = new UserGradeModel();
        $res = $userGradeModel->updateInfo(['id'=>$receive['id']],$receive);
        if ($res){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败');
        }

    }
}