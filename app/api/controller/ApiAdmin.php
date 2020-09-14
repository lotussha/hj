<?php


namespace app\api\controller;


use app\apiadmin\model\AdminUsers;
use app\Request;
use think\App;
use think\Collection;

class ApiAdmin extends Collection
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
//        'api/goods._goods/goods_details',
    ];
    protected $controller;
    protected $action;
    protected $url;

    /**
     * 当前用户
     * @var AdminUsers
     */
    protected $api_admin_user;

    protected $admin_id = 0;

    /**
     * 前端变量
     * @var array
     */
    protected $api_admin;


    /**
     * 构造方法
     * Api constructor.
     * @param Request $request
     * @param App $app
     */
    public function __construct(Request $request, App $app)
    {
        $this->app = $app;
        $this->request = $request;
        $this->param = $this->request->param();
        $this->controller = $this->request->controller();
        $this->action = $this->request->action();
        $this->url = parse_name(app('http')->getName()) . '/' . parse_name($this->controller) . '/' . parse_name($this->action);
        $this->token = $this->request->header('token');
        $this->_initialize();
        parent::__construct();
    }

    /**
     * 控制器初始化
     * User: hao  2020-09-05
     */
    public function _initialize()
    {
        //需要验证
        if (!in_array($this->url, $this->authExcept) && true == $this->needAuth) {
            //缺少token
            if (is_null($this->token) || empty($this->token)) {
                echo json_encode(['status'=>'0','code'=>"10002",'msg'=>'请先登录']);die;
            }

            $checkToken = checkToken($this->token);
            if($checkToken['status'] == 0){
                echo json_encode(['status'=>'0','code'=>"10000",'msg'=>$checkToken['msg']]);die;
            }
            //用户信息
            $this->admin_id = $checkToken['data']->admin_id;
            $this->api_admin_user = AdminUsers::find($this->admin_id);
            $this->api_admin_user['identity']= $checkToken['data']->role_type ?? '1';
            $this->api_admin_user['identity_id']= $this->admin_id;

        }

        //分页记录数处理
        $this->api_admin['list_rows'] = $this->param['list_rows'] ?? 10;
    }


}