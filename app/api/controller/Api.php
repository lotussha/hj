<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/20
 * Time: 11:36
 */

namespace app\api\controller;

use app\Request;
use think\App;
use think\Collection;
use app\common\model\user\UserModel;
class Api extends Collection
{
    //应用实例
    protected $app;
    //Request实例
    protected $request;
    //请求参数
    protected $param;
    //token
    protected $token;
    //是否需要验证token
    protected $needAuth = true;

    //无需验证权限的url
    protected $authExcept = [
        'api/goods._goods/goods_details',
        'api/index/index',
    ];

    protected $controller;
    protected $action;
    protected $url;

    /**
     * 当前用户
     * @var UserModel
     */
    protected $api_user;

    protected $user_id = 0;

    /**
     * 前端变量
     * @var array
     */
    protected $api;
    /**
     * 构造方法
     * Api constructor.
     * @param Request $request
     * @param App $app
     */
    public function __construct(Request $request,App $app)
    {
        $this->app = $app;
        $this->request = $request;
        $this->param = $this->request->param();
        $this->controller = $this->request->controller();
        $this->action = $this->request->action();
        $this->url = parse_name(app('http')->getName()) . '/' . parse_name($this->controller) . '/' . parse_name($this->action);
        $this->token   = $this->request->header('token');
        $this->_initialize();
        parent::__construct();
    }

    /**
     * 控制器初始化
     * User: Jomlz
     * User: hao 补充  2020-8-21
     */
    public function _initialize()
    {

        //需要验证
        if (!in_array($this->url, $this->authExcept) && true == $this->needAuth){
            //缺少token
            if (is_null($this->token) || empty($this->token)) {
                echo json_encode(['status'=>'0','code'=>"10002",'msg'=>'请先登录']);die;
            }

            //验证是否登录
            $checkToken = apiCheckToken($this->token);

            if($checkToken['status'] == 0){
                echo json_encode(['status'=>'0','code'=>"10000",'msg'=>$checkToken['msg']]);die;
            }
            $this->user_id = $checkToken['data']->api_id;
            //用户信息
            $this->api_user = UserModel::find($this->user_id);
        }
        //不需要登录验证页面但已登录获取用户信息
        if (!empty($this->token)){
            $checkToken = apiCheckToken($this->token);
            if($checkToken['status'] == 0){
                echo json_encode(['status'=>'0','code'=>"10000",'msg'=>$checkToken['msg']]);die;
            }
            $this->user_id = $checkToken['data']->api_id;
            //用户信息
            $this->api_user = UserModel::find($this->user_id);
        }
        //分页记录数处理
        $this->api['list_rows'] = $this->param['list_rows'] ?? 10;
    }
}