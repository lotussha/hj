<?php


namespace app\api\controller\user;


use app\api\controller\Api;
use app\common\model\user\UserBankModel;
use app\common\validate\user\UserBankValidate;
use sakuno\utils\JsonUtils;
use think\App;
use think\Request;

//用户银行卡
class UserBank extends Api
{
    protected $model;
    protected $validate;
        public function __construct(Request $request, App $app)
    {

        $this->model = new UserBankModel();
        $this->validate = new UserBankValidate();
        parent::__construct($request, $app);
    }

    /**
     * 用户银行卡列表
     * User: hao  2020-8-21
     */
    public function index(){

        $uid = $this->api_user['id'];
        $data = $this->param;
        $data['uid'] = $uid;
        $validate_resule = $this->validate->scene('list')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($this->validate->getError(), PARAM_IS_INVALID);
        }

        $where = array();
        $where[] = ['uid','=',$uid];
        $data['where'] = $where;
        $data['field'] = 'id,name,card,issuing_bank,phone,idcard';

        $lists = $this->model->getCommonLists($data);

        return JsonUtils::successful('操作成功',$lists);
    }

    /**
     * 用户银行卡详情
     * User: hao  2020-8-21
     */
    public function info(){
        $uid = $this->api_user['id'];
        $data = $this->param;
        $data['uid'] = $uid;
        $validate_resule = $this->validate->scene('info')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($this->validate->getError(), PARAM_IS_INVALID);
        }

        $where = array();
        $where[] = ['uid','=',$uid];
        $where[] = ['is_delete','<>',1];
        $where[] = ['id','=',$data['id']];
        $field = 'id,name,card,issuing_bank,phone,idcard';
        $lists = $this->model->findInfo($where,$field);
        return JsonUtils::successful('操作成功',$lists);
    }


    /**
     * 用户银行卡添加
     * User: hao  2020-8-21
     */
    public function add(){
        $uid = $this->api_user['id'];
        $data = $this->param;
        $data['uid'] = $uid;
        $validate_resule = $this->validate->scene('add')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($this->validate->getError(), PARAM_IS_INVALID);
        }

        $this->model->beginTrans();
        //默认
        if (isset($data['is_default'])==1){
            $this->model->updateInfo(['uid'=>$data['uid']],['is_default'=>0]);
        }

        $rs = $this->model->addInfo($data);
        if (!$rs){
            $this->model->rollbackTrans();
            return JsonUtils::fail('操作失败', PARAM_IS_INVALID);
        }

        $this->model->commitTrans();
        return JsonUtils::successful('操作成功');
    }


    /**
     * 用户银行卡修改
     * User: hao  2020-8-21
     */
    public function edit(){
        $uid = $this->api_user['id'];
        $data = $this->param;
        $data['uid'] = $uid;
        $validate_resule = $this->validate->scene('edit')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($this->validate->getError(), PARAM_IS_INVALID);
        }
        $this->model->beginTrans();
        //默认
        if (isset($data['is_default'])==1){
            $this->model->updateInfo(['uid'=>$data['uid']],['is_default'=>0]);
        }

        $where = array();
        $where[] = ['id','=',$data['id']];
        $where[] = ['uid','=',$uid];
        $rs = $this->model->updateInfo($where,$data);
        if (!$rs){
            $this->model->rollbackTrans();

            return JsonUtils::fail('操作失败', PARAM_IS_INVALID);
        }
        $this->model->commitTrans();
        return JsonUtils::successful('操作成功');
    }

    /**
     * 用户银行卡删除
     * User: hao  2020-8-21
     */
    public function del(){
        $uid = $this->api_user['id'];
        $data = $this->param;
        $data['uid'] = $uid;
        $validate_resule = $this->validate->scene('del')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($this->validate->getError(), PARAM_IS_INVALID);
        }
        $where = array();
        $where[] = ['id','=',$data['id']];
        $where[] = ['uid','=',$uid];
        $rs = $this->model->deleteInfo($where);
        if (!$rs){
            return JsonUtils::fail('操作失败', PARAM_IS_INVALID);
        }
        return JsonUtils::successful('操作成功');
    }

}