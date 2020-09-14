<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/7/31
 * Time: 17:12
 */

namespace app\apiadmin\controller;

use app\apiadmin\model\AdminMenuModel;
use app\apiadmin\model\AdminUsers;
use app\Request;
use think\App;
use think\Collection;
use tools\{AdminAuth};

class Base extends Collection
{
    use AdminAuth;
    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;
    //Request实例
    protected $request;
    //请求参数
    protected $param;
    //token
    protected $token;
    //是否需要验证token
    protected $needAuth = true;
    /**
     * 当前用户
     * @var AdminUsers
     */
    protected $admin_user;
    /**
     * 后台变量
     * @var array
     */
    protected $admin;

    /**
     * 无需验证权限的url
     * @var array
     */
    protected $authExcept = [
//        'admin/auth/login',
//        'admin/auth/logout',
//        'admin/auth/get_geetest_status',
//        'admin/editor/server',
    ];


    protected $controller;
    protected $action;
    protected $url;

    /**
     * 构造方法
     * Base constructor.
     * @param Request $request
     * @param App $app
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function __construct(Request $request,App $app)
    {
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Headers: *');
        header('Access-Control-Allow-Methods: *');
//        header('Access-Control-Max-Age: 3600');  //前台缓存这个信息
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
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function _initialize()
    {
        //需要验证
        if (!in_array($this->url, $this->authExcept) && true == $this->needAuth) {
            //缺少token
            if (is_null($this->token) || empty($this->token)) {
                echo json_encode(['status'=>'0','code'=>"10002",'msg'=>'请先登录']);die;
            }
            //验证是否登录
            $checkToken = checkToken($this->token);
            if($checkToken['status'] == 0){
                echo json_encode(['status'=>'0','code'=>"10000",'msg'=>$checkToken['msg']]);die;
            }
            //用户信息
            $this->admin_user = AdminUsers::find($checkToken['data']->admin_id);
            $this->admin_user['identity']= $checkToken['data']->role_type ?? '1';
            $this->admin_user['identity_id']= $checkToken['data']->admin_id;
//            $this->admin_user['identity'] = 2;
//            $this->admin_user['identity_id'] = 5;
            if ($this->admin_user['identity'] != 1){
                //不是平台类型不能传的参
                if (isset($this->param['identity']) || isset($this->param['identity_id'])){
                    echo json_encode(['status'=>'0','code'=>"10000",'msg'=>'无权限传参']);die;
                }
                $this->param['identity'] = $this->admin_user['identity'];
                $this->param['identity_id'] = $this->admin_user['identity_id'];
            }
            //权限判断
            if (!$this->authCheck($this->admin_user) && $this->admin_user['id'] != 1) {
                echo json_encode(['status'=>'0','code'=>"10003",'msg'=>'无权限']);die;
            }
        }

        //记录日志
        $menu = AdminMenuModel::where(['url' => $this->url])->find();
        if ($menu) {
            $this->admin['title'] = $menu->name;
            if ($menu->log_type === $this->request->method()) {
//                $this->createAdminLog($this->user, $menu);  //日志记录暂时不做
            }
        }
        //分页记录数处理
        $this->admin['list_rows'] = $this->param['list_rows'] ?? 10;

    }

    public function __call($fun,$arg)
    {
        $arr = array_return();
        $arr['status'] = '0';
        $arr['code'] = '10001';
        return return_json($arr);
    }
}