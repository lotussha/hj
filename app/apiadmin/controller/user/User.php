<?php


namespace app\apiadmin\controller\user;


use app\apiadmin\controller\Base;
use app\common\logic\user\UserLogic;
use app\common\model\user\UserCommissionLogModel;
use app\common\model\user\UserDetailsModel;
use app\common\model\user\UserIntegralLogModel;
use app\common\model\user\UserModel;
use app\common\validate\user\UserValidate;
use app\Request;
use sakuno\services\UtilService;
use sakuno\utils\JsonUtils;
use think\Collection;

//用户
class User extends Base
{
    /**
     * 用户列表
     * @param string   page  页数
     * @param string   keyword      搜索内容
     * @param string   grade_id 等级
     * @return array
     * @author hao    2020.08.14
     * */
    public function index(UserModel $userModel)
    {
        $data = $this->param;
        $data['list_rows'] = $this->admin['list_rows'];
        $data['filed'] = '*';
        $res = $userModel->getAllUser($data);
        return JsonUtils::successful('操作成功',$res);
    }

    /**
     * 用户详情
     * @param string   id 用户ID
     * @return array
     * @author hao    2020.08.14
     * */
    public function info(UserModel $userModel, UserValidate $validate)
    {
        //检验
        $validate_result = $validate->scene('info')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(), '00000');
        }
        //路由
        $lists = $userModel->getInfoUser(['id' => $this->param['id']]);
        return JsonUtils::successful('获取成功', $lists);
    }

    /**
     * 查看用户下级（团队）
     * @param string   id 用户ID
     * @return array
     * @author hao    2020.08.14
     * */
    public function team(UserModel $userModel, UserValidate $validate)
    {
        $data = $this->param;

        //检验
        $validate_result = $validate->scene('info')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }

        //获取全部ID
        $all = $userModel->getAddId();
        $page = isset($data['page']) ? $data['page'] : 1;
        //处理
        $Logic = new UserLogic();
        $lists = $Logic->team($all, $this->param['id'], $page,$this->admin['list_rows']);

        return JsonUtils::successful('获取成功', $lists);
    }

    /**
     * 添加用户数据
     * @param string   is_touch    1:一键生成  2：手动生成
     * @param string   share_id      分享人的id 默认0
     * @param string   username 用户账号
     * @param string   grade_id 身份id
     * @param string   avatar_url 用户头像
     * @param string   nick_name 用户昵称
     * @return array
     * @author hao    2020.08.13
     * */
    public function add()
    {
        $data = $this->param;
        $validate = new UserValidate();
        //检验
        $validate_resule = $validate->scene('add')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError());
        }

        //处理数据
        $logic = new UserLogic();
        $data = $logic->addhandle($data);
        if (isset($data['data_code']) && $data['data_code'] === false) {
            return JsonUtils::fail($data['data_msg']);
        }
        //模型
        $UserMode = new UserModel();
        $UserDetails = new UserDetailsModel();

        try {
            $UserMode->beginTrans();
            $id = $UserMode->addInfoId($data);
            if (!$id) {
                return JsonUtils::fail('添加用户失败');
            }
            $rs = $UserDetails->saveInfo(['uid' => $id]);
            if (!$rs) {
                $UserMode->rollbackTrans();
                return JsonUtils::fail('添加用户详情失败');
            }
            $UserMode->commitTrans();
            return JsonUtils::successful('操作成功');
        } catch (\Exception $e) {
            $UserMode->rollbackTrans();
            return JsonUtils::fail('数据异常');
        }
    }

    /**
     * 增加用户积分
     * @param string   id  用户
     * @param string   integral 增加 积分
     * @return array
     * @author hao    2020.08.15
     * */
    public function integral(Request $request)
    {
        list($id, $integral,$remark) = UtilService::postMore([
            ['id', ''],
            ['integral', ''],
            ['remark', '']
        ], $request, true);

        $validate = new UserValidate();
        //检验
        $validate_resule = $validate->scene('integral')->check(['id' => $id, 'integral' => $integral]);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        //处理
        $aid = $this->admin_user['id'];
        $Logic = new UserLogic();
        $rs = $Logic->add_integral($id, $integral,$aid,$remark);
        return $rs;
    }

    /**
     * 增加用户佣金
     * @param string   id  用户
     * @param string   commission 增加 佣金金额
     * @return array
     * @author hao    2020.08.15
     * */
    public function commission(Request $request){
        list($id, $commission,$remark) = UtilService::postMore([
            ['id', ''],
            ['commission', ''],
            ['remark', '']
        ], $request, true);

        //检验
        $validate = new UserValidate();
        $validate_resule = $validate->scene('commission')->check(['id' => $id, 'commission' => $commission]);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $aid = $this->admin_user['id'];
        //处理
        $Logic = new UserLogic();
        $rs = $Logic->add_commission($id, $commission,$aid,$remark);
        return $rs;
    }

    /**
     * 增加用户充值金额
     * @param string   id  用户
     * @param string   recharge 增加 充值金额
     * @return array
     * @author hao    2020.08.15
     * */
    public function recharge(Request $request){
        list($id, $recharge,$remark) = UtilService::postMore([
            ['id', ''],
            ['recharge', ''],
            ['remark', '']
        ], $request, true);

        //检验
        $validate = new UserValidate();
        $validate_resule = $validate->scene('recharge')->check(['id' => $id, 'recharge' => $recharge]);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }

        $aid = $this->admin_user['id'];

        //处理
        $Logic = new UserLogic();
        $rs = $Logic->add_recharge($id, $recharge,$aid,$remark);
        return $rs;
    }

    /**
     * 积分列表详情
     * @param string   uid  用户
     * @param string   integral_type 积分分类1：冻结积分 2:增加使用积分  3：消费积分
     * @param string   page 页数
     * @return array
     * @author hao    2020.08.15
     * */
    public function integral_list(){
        //获取数据
        $data = $this->param;
        $lists = ( new UserIntegralLogModel())->getAllIntegral($data);
        return JsonUtils::successful('操作成功', $lists);
    }



    /**
     * 佣金列表详情
     * @return array
     * @author hao    2020.08.31
     * */
    public function commission_list(){
        //获取数据
        $data = $this->param;
        $lists = ( new UserCommissionLogModel())->getAllCommission($data);
        return JsonUtils::successful('操作成功', $lists);
    }


    /**
     * 金额列表详情
     * @param string   uid  用户
     * @param string   type 类型： 101：冻结佣金，102：可提佣金 ，103：后台添加佣金，201：用户充值，202：后台充值，301：分红，401：提现，
     * @param string   money_type 分类1:充值  2：佣金
     * @param string   page 页数
     * @return array
     * @author hao    2020.08.15
     * */
    public function money_list(Request $request){
        //获取数据
        list($uid, $type,$money_type,$page) = UtilService::postMore([
            ['uid', ''],
            ['type', ''],
            ['money_type', ''],
            ['page', '1'],
        ], $request, true);

        //处理
        $Logic = new UserLogic();

        $receive = array();
        $receive['page'] = $page;
        $receive['list_row'] = $this->admin['list_rows'];
        $receive['type'] =$type;
        $receive['money_type'] =$money_type;
        $receive['uid'] =$uid;
        $rs = $Logic->getmoney($receive);
        return $rs;
    }

    /**
     * 修改用户上级、禁用，修改等级
     * @param string   uid  用户
     * @param string   pid  上级id
     * @param string   grade_id  等级id
     * @param string   status  状态1：启用 2：禁用
     * @return array
     * @author hao    2020.08.15
     * */
    public function modify(Request $request){
        //获取数据
        list($id, $pid,$grade_id,$status) = UtilService::postMore([
            ['id', ''],
            ['pid', ''],
            ['grade_id', ''],
            ['status', ''],
        ], $request, true);

        //检验
        $validate = new UserValidate();
        $validate_resule = $validate->scene('info')->check(['id' => $id]);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }

        //处理
        $Logic = new UserLogic();

        $receive = array();
        $receive['id'] = $id;
        $receive['pid'] = $pid;
        $receive['grade_id'] =$grade_id;
        $receive['status'] =$status;
        $receive['aid'] =$this->admin_user['id'];
        $rs = $Logic->modify($receive);
        return $rs;

    }
}