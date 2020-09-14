<?php


namespace app\api\controller\user;


use app\api\controller\Api;
use app\common\logic\user\UserAddressLogic;
use app\common\model\user\UserAddressModel;
use app\common\validate\user\UserAddressValidate;
use app\Request;
use sakuno\utils\JsonUtils;
use think\App;

//用户地址
class UserAddress extends Api
{

    public function __construct(Request $request, App $app)
    {
        parent::__construct($request, $app);
    }

    /**
     * 用户地址列表
     * User: hao  2020-8-21
     */
    public function index()
    {

        $uid = $this->api_user['id'];
        $data = $this->param;
        $data['user_id'] = $uid;

        $validate = new UserAddressValidate();
        $validate_resule = $validate->scene('list')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $logic = new UserAddressLogic();

        $res = $logic->lists($data);
        return $res;
    }

    /**
     * 用户地址详情
     * User: hao 2020-8-21
     */
    public function info()
    {
        $uid = $this->api_user['id'];
        $data = $this->param;
        $data['user_id'] = $uid;
        $validate = new UserAddressValidate();
        $validate_resule = $validate->scene('info')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }

        $logic = new UserAddressLogic();
        $rs = $logic->info($data);
        return $rs;

    }

    /**
     * 用户地址添加
     * User: hao 2020-8-21
     */
    public function add()
    {
        $uid = $this->api_user['id'];
        $data = $this->param;
        $data['user_id'] = $uid;
        $validate = new UserAddressValidate();
        $validate_resule = $validate->scene('add')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $model = new UserAddressModel();
        $model->beginTrans();
        //默认
        if (isset($data['is_default'])==1){
            $model->updateInfo(['user_id'=>$data['user_id']],['is_default'=>0]);
        }

        $res = $model->addInfo($data);
        if (!$res) {
            $model->rollbackTrans();
            return JsonUtils::fail('操作失败');
        }

        $model->commitTrans();
        return JsonUtils::successful('操作成功');
    }

    /**
     * 用户地址修改
     * User: hao 2020-8-21
     */
    public function edit(){
        $uid = $this->api_user['id'];
        $data = $this->param;
        $data['user_id'] = $uid;
        $validate = new UserAddressValidate();
        $validate_resule = $validate->scene('edit')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $model = new UserAddressModel();
        $model->beginTrans();
        //默认
        if (isset($data['is_default'])==1){
            $model->updateInfo(['user_id'=>$data['user_id']],['is_default'=>0]);
        }

        $res = $model->updateInfo(['address_id'=>$data['address_id']],$data);
        if (!$res) {
            $model->rollbackTrans();
            return JsonUtils::fail('操作失败');
        }
        $model->commitTrans();
        return JsonUtils::successful('操作成功');
    }

    /**
     * 用户地址删除
     * User: hao 2020-8-21
     */
    public function del(){
        $uid = $this->api_user['id'];
        $data = $this->param;
        $data['user_id'] = $uid;
        $validate = new UserAddressValidate();
        $validate_resule = $validate->scene('del')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $model = new UserAddressModel();
        $res = $model->deleteInfo(['address_id'=>$data['address_id']]);
        if (!$res) {
            return JsonUtils::fail('操作失败');
        }
        return JsonUtils::successful('操作成功');
    }


}