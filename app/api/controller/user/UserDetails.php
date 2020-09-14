<?php


namespace app\api\controller\user;


use app\api\controller\Api;
use app\common\logic\user\UserDetailsLogic;
use app\common\logic\user\UserMethodLogic;
use app\common\model\config\WebsiteConfigModel;
use app\common\model\user\UserDetailsModel;
use app\common\model\user\UserModel;
use app\common\validate\user\UserApiValidate;
use sakuno\utils\JsonUtils;
use think\App;
use think\facade\Db;
use think\Request;

//用户详情
class UserDetails extends Api
{
    protected $detailsLogic;
    protected $validate;

    public function __construct(Request $request, App $app)
    {
        parent::__construct($request, $app);
        $this->detailsLogic = new UserDetailsLogic();
        $this->validate = new UserApiValidate();
    }


    /**
     * 修改个人资料
     * User: hao  2020-8-22
     */
    public function edit_information(){

        $data = $this->param;
        $data['uid'] = $this->api_user['id'];
        $rs =  $this->detailsLogic->edit_information($data);
        return $rs;
    }

    /**
     * 用户分享图片
     * User: hao  2020-8-22
     */
    public function share(){
        $data = $this->param;

        //检验
        $validate_resule = $this->validate->scene('share')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($this->validate->getError(), PARAM_IS_INVALID);
        }
        $logic = new UserDetailsLogic();
        $rs = $logic->share($data);
        return $rs;
    }

    /**
     * 海报图片分享设置
     * User: hao  2020-8-22
     */
    public function poster(){
        $website = new WebsiteConfigModel();
        $website_list = $website->where('type', 'in', 'user_poster_img,user_poster_name_color')->field('type,val')->column('val', 'type');
        $website_list['user_poster_img'] = explode(",", $website_list['user_poster_img']);
        return JsonUtils::successful('操作成功',$website_list);
    }

    /**
     * 用户充值
     * User: hao  2020.8.22
     */
    public function recharge(){

        $data = $this->param;
        //检验
        $validate_resule = $this->validate->scene('recharge')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($this->validate->getError(), PARAM_IS_INVALID);
        }
        $logic = new UserDetailsLogic();
        $data['uid'] = $this->api_user['id'];
        $data['openid'] = $this->api_user['openid'];
        $rs = $logic->recharge($data);
        return $rs;
    }

    /**
     * 获取可提现金额和手续费
     * User: hao  2020.8.24
     */
    public function get_cash(){
        $logic = new UserDetailsLogic();
        $data = $this->param;
        $data['uid'] = $this->api_user['id'];
        $data['grade_id'] = $this->api_user['grade_id'];
        $rs = $logic->get_cash($data);
        return JsonUtils::successful('操作成功',$rs);
    }

    /**
     * 用户提现
     * User: hao  2020.8.24
     */
    public function cash(){
        $data = $this->param;
        //检验
        $validate_resule = $this->validate->scene('cash')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($this->validate->getError(), PARAM_IS_INVALID);
        }
        $logic = new UserDetailsLogic();
        $data['uid'] = $this->api_user['id'];
        $data['grade_id'] = $this->api_user['grade_id'];

        $rs = $logic->cash($data);
        return $rs;
    }


    /**
     * 个人中心
     * User: hao  2020.8.27
     */
    public function personal(){
        $uid =  $this->api_user['id'];
        $logic = new UserDetailsLogic();
        $rs = $logic->personal($uid);
        return $rs;
    }

    /**
     * 签到页面
     * User: hao  2020.8.27
     */
    public function sign_page(){

        $uid =  $this->api_user['id'];
        $logic = new UserDetailsLogic();
        $rs = $logic->sign_page($uid);
        return $rs;
    }

    /**
     * 用户签到
     * User: hao  2020.8.27
     */
    public function sign_user(){
        $uid =  $this->api_user['id'];
        $logic = new UserDetailsLogic();
        $rs = $logic->sign_user($uid);
        return $rs;
    }

    /**
     * 用户团队
     * User: hao  2020.09.02
     */
    public function team(){
        $data = $this->param;
        //检验
        $validate_resule = $this->validate->scene('team')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($this->validate->getError(), PARAM_IS_INVALID);
        }
        $data['uid'] = $this->api_user['id'];
        $logic = new UserDetailsLogic();
        $rs = $logic->team($data);
        return $rs;
    }

    /**
     * 修改支付密码
     * User: hao  2020.09.09
     */
    public function modify_payment_password(){
        $data = $this->param;
        //检验
        $validate_resule = $this->validate->scene('modify_payment_password')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($this->validate->getError(), PARAM_IS_INVALID);
        }
        $data['phone'] = $this->api_user['phone'];
        $data['uid'] = $this->api_user['id'];
        $logic = new UserDetailsLogic();
        $rs = $logic->modify_payment_password($data);
        return $rs;
    }

    /**
     * 绑定手机号
     * User: hao  2020.09.02
     */







    /**
     * 测试支付金额
     * User: hao  2020.8.24
     */
    public function order_balance(){
        $order = input('order_sn','');

        Db::startTrans();
        $data =[
            'order_sn'=>$order,
            'money'=>'100',
            'uid'=>'38',
        ];
        $res = (new UserMethodLogic())->order_balance($data);
        Db::commit();
        dump($res);
    }
    /**
     * 测使用金额支付订单退款
     * User: hao  2020.8.24
     */
    public function refund_balance(){
        $order = input('order_sn','');

        Db::startTrans();
        $data =[
            'order_sn'=>$order,
            'money'=>'100',
            'uid'=>'38',
        ];
        $res = (new UserMethodLogic())->refund_balance($data);

        if (!$res){
            Db::rollback();
        }

        Db::commit();
        dump($res);
    }    /**
 *
     * 测订单完成 （不会退款）
     * User: hao  2020.8.24
     */
    public function order_completion(){
        $order = input('order_sn','');

        Db::startTrans();
        $data =[
            'order_sn'=>$order,
            'money'=>'100',
            'uid'=>'38',
            'is'=>1,
        ];

        $res = (new UserMethodLogic())->order_completion($data);

        if (!$res){
            Db::rollback();
        }

        Db::commit();
        dump($res);
    }




}