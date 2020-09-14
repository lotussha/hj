<?php


namespace app\apiadmin\controller\config;


use app\apiadmin\controller\Base;
use app\common\logic\config\RechargeOptionLogic;
use app\common\model\config\RechargeOptionModel;
use app\common\validate\config\RechargeOptionValidate;
use app\Request;
use sakuno\services\UtilService;
use sakuno\utils\JsonUtils;

//充值选项
class RechargeOption extends Base
{
    /**
     * 充值选项列表
     * @return array
     * @author hao    2020.08.18
     * */
    public function index(){
        $model =new RechargeOptionModel();
        $where = array();
        $where[]  = ['is_delete','<>','1'];
        $lists = $model->getList($where,'id,min_money,max_money,give,status');
        return JsonUtils::successful('操作成功',$lists);
    }

    /**
     * 充值选项详情
     * @return array
     * @author hao    2020.08.18
     * */
    public function info(Request $request){
        //获取数据
        list($id) = UtilService::postMore([
            ['id', ''],
        ], $request, true);

        $validate = new RechargeOptionValidate();
        $validate_resule = $validate->scene('info')->check(['id' => $id]);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $model =new RechargeOptionModel();
        $lists = $model->findInfo(['id'=>$id],'id,min_money,max_money,give,status');
        return JsonUtils::successful('操作成功',$lists);
    }

    /**
     * 充值选项添加
     * @return array
     * @author hao    2020.08.18
     * */
    public function add(Request $request){
        //获取数据
        list($min_money,$max_money,$give) = UtilService::postMore([
            ['min_money', ''],
            ['max_money', ''],
            ['give', ''],
        ], $request, true);
        $data = array();
        $data['min_money'] = $min_money;
        $data['max_money'] = $max_money;
        $data['give'] = $give;
        $validate = new RechargeOptionValidate();
        $validate_resule = $validate->scene('add')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }

        $Logic = new RechargeOptionLogic();
        $data = $Logic->handle($data);

        if (isset($data['data_code']) && $data['data_code']===false){
            return JsonUtils::fail($data['data_msg'], PARAM_IS_INVALID);
        }
        $model =new RechargeOptionModel();
        $res = $model->addInfo($data);
        if (!$res){
            return JsonUtils::fail('操作失败', PARAM_IS_INVALID);
        }
        return JsonUtils::successful('操作成功');
    }

    /**
     * 充值选项修改
     * @return array
     * @author hao    2020.08.18
     * */
    public function edit(Request $request){
        //获取数据
        list($id,$min_money,$max_money,$give) = UtilService::postMore([
            ['id', ''],
            ['min_money', ''],
            ['max_money', ''],
            ['give', ''],
        ], $request, true);
        $data = array();
        $data['id'] = $id;
        $data['min_money'] = $min_money;
        $data['max_money'] = $max_money;
        $data['give'] = $give;
        $validate = new RechargeOptionValidate();
        $validate_resule = $validate->scene('edit')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }

        $Logic = new RechargeOptionLogic();
        $data = $Logic->handle($data,'edit');

        if (isset($data['data_code']) && $data['data_code']===false){
            return JsonUtils::fail($data['data_msg'], PARAM_IS_INVALID);
        }

        $model =new RechargeOptionModel();
        $res = $model->updateInfo(['id'=>$id],$data);
        if (!$res){
            return JsonUtils::fail('操作失败', PARAM_IS_INVALID);
        }
        return JsonUtils::successful('操作成功');
    }

    /**
     * 充值选项禁用
     * @return array
     * @author hao    2020.08.18
     * */
    public function status(Request $request){
        //获取数据
        list($id,$status) = UtilService::postMore([
            ['id', ''],
            ['status', ''],
        ], $request, true);
        $data = array();
        $data['id'] = $id;
        $data['status'] = $status;
        $validate = new RechargeOptionValidate();
        $validate_resule = $validate->scene('status')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $model =new RechargeOptionModel();
        $res = $model->updateInfo(['id'=>$id],$data);
        if (!$res){
            return JsonUtils::fail('操作失败', PARAM_IS_INVALID);
        }
        return JsonUtils::successful('操作成功');
    }

    /**
     * 充值选项禁用
     * @return array
     * @author hao    2020.08.18
     * */
    public function del(Request $request){
        //获取数据
        list($id) = UtilService::postMore([
            ['id', ''],
        ], $request, true);

        $validate = new RechargeOptionValidate();
        $validate_resule = $validate->scene('del')->check(['id'=>$id]);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $model =new RechargeOptionModel();
        $res = $model->deleteInfo(['id'=>$id]);
        if (!$res){
            return JsonUtils::fail('操作失败', PARAM_IS_INVALID);
        }
        return JsonUtils::successful('操作成功');
    }
}