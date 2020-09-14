<?php


namespace app\api\controller\user;


use app\api\controller\Api;
use app\common\logic\settlement\SettlementLogic;
use app\common\model\settlement\SettlementModel;
use app\common\validate\settlement\SettlementValidate;
use app\Request;
use sakuno\utils\JsonUtils;
use think\App;
use think\cache\driver\Redis;
use think\facade\Cache;

//用户入驻
class Settlement extends Api
{


    protected $validate;
    protected $logic;
    public function __construct(Request $request, App $app)
    {
        $this->validate = new SettlementValidate();
        $this->logic = new SettlementLogic();
        parent::__construct($request, $app);
    }

    /**
     * 用户入驻
     * User: hao  2020-8-29
     */
    public function add_settlement(){
        $data = $this->param;
        $data['uid'] = $this->api_user['id'];
        //检验
        $validate_resule = $this->validate->scene('add')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($this->validate->getError(), PARAM_IS_INVALID);
        }
        $res = $this->logic->userAddHandle($data);
        return $res;
    }

    /**
     * 用户入驻详情
     * User: hao  2020-9-1
     */
    public function settlement_details(){
        $data = $this->param;
        $data['uid'] = $this->api_user['id'];
        $list = (new SettlementModel())->getInfoSettlement(['uid'=>$data['uid']]);
        return JsonUtils::successful('操作成功',$list);
    }

    /**
     * 用户入驻修改
     * User: hao  2020-9-1
     */
    public function edit_settlement(){
        $data = $this->param;
        $data['uid'] = $this->api_user['id'];
        //检验
        $validate_resule = $this->validate->scene('edit')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($this->validate->getError(), PARAM_IS_INVALID);
        }
        $res = $this->logic->userEditHandle($data);
        return $res;
    }





}