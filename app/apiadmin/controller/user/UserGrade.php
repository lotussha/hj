<?php


namespace app\apiadmin\controller\user;


use app\apiadmin\controller\Base;
use app\common\logic\user\UserGradeLogic;
use app\common\model\user\UserGradeModel;
use app\common\validate\user\UserGradeValidate;
use app\Request;
use sakuno\services\UtilService;
use sakuno\utils\JsonUtils;
use think\facade\Request as Requests; //
//等级
class UserGrade extends Base
{

    /**
     * 等级列表
     * @param string   page  页数
     * @return array
     * @author hao    2020.08.15
     * */
    public function index(Request $request){
        list($page,) = UtilService::postMore([
            ['page', '1'],
        ], $request, true);
        $UserGrade = new UserGradeModel();
        $lists = $UserGrade->getListPage('',$page,$this->admin['list_rows']);
        return JsonUtils::successful('获取成功', ['list'=>$lists]);
    }

    /**
     * 等级详情
     * @param string   id  等级id
     * @return array
     * @author hao    2020.08.15
     * */
    public function info(Request $request){
        list($id) = UtilService::postMore([
            ['id', ''],
        ], $request, true);
        //检验
        $validate = new UserGradeValidate();
        $validate_resule = $validate->scene('info')->check(['id' => $id]);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), 10001);
        }

        $UserGrade = new UserGradeModel();
        $lists = $UserGrade->findInfo(['id'=>$id]);
        return JsonUtils::successful('获取成功', $lists, '10000');
    }

    /**
     * 等级添加
     * @param string   name  等级名称
     * @param string   full_money  购买产品多少钱升级
     * @param string   is_shareholder  1:是股东  0不是
     * @param string   bonus  分红 占比 %
     * @param string   share_num  直推人数升级
     * @param string   status  1:启用 2：禁用
     * @param string   cash_lowest  提现最低金额
     * @param string   cash_upper  提现上限
     * @param string   cash_charge  提现手续费 %
     * @param string   discount  商品折扣价 %，如果设置98%，商品价格*98%
     * @return array
     * @author hao    2020.08.15
     * */
    public function add(Requests $request){
        $data = $request::only([
            'name'=>'',
            'full_money'=>'',
            'is_shareholder'=>'',
            'bonus'=>'',
            'share_num'=>'',
            'status'=>'',
            'cash_lowest'=>'',
            'cash_upper'=>'',
            'cash_charge'=>'',
            'discount'=>'',
            'cash_num'=>'',
            ]);
        //检验
        $validate = new UserGradeValidate();
        $validate_resule = $validate->scene('add')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), 10001);
        }
        $logic = new UserGradeLogic();
        $res = $logic->addGrade($data);
        return $res;
    }

    /**
     * 等级修改
     * @param string   id  等级id
     * @param string   name  等级名称
     * @param string   full_money  购买产品多少钱升级
     * @param string   is_shareholder  1:是股东  0不是
     * @param string   bonus  分红 占比 %
     * @param string   share_num  直推人数升级
     * @param string   status  1:启用 2：禁用
     * @param string   cash_lowest  提现最低金额
     * @param string   cash_upper  提现上限
     * @param string   cash_charge  提现手续费 %
     * @param string   discount  商品折扣价 %，如果设置98%，商品价格*98%
     * @return array
     * @author hao    2020.08.15
     * */
    public function edit(Requests $request){
        $data = $request::only([
            'id'=>'',
            'name'=>'',
            'full_money'=>'',
            'is_shareholder'=>'',
            'bonus'=>'',
            'share_num'=>'',
            'status'=>'',
            'cash_lowest'=>'',
            'cash_upper'=>'',
            'cash_charge'=>'',
            'discount'=>'',
            'cash_num'=>'',
        ]);
        //检验
        $validate = new UserGradeValidate();
        $validate_resule = $validate->scene('edit')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), 10001);
        }
        $logic = new UserGradeLogic();
        $res = $logic->editGrade($data);
        return $res;
    }

    /**
     * 等级修改
     * @param string   id  等级id
     * @param string   status  1:启用 2：禁用
     * @return array
     * @author hao    2020.08.15
     * */
    public function status(Request $request){
        list($id,$status) = UtilService::postMore([
            ['id', ''],
            ['status', ''],
        ], $request, true);
        //检验
        $validate = new UserGradeValidate();
        $validate_resule = $validate->scene('status')->check(['id' => $id,'status'=>$status]);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), 10001);
        }
        $data = array();
        $data['id']=$id;
        $data['status']=$status;
        $logic = new UserGradeLogic();
        $res = $logic->statusGrade($data);
        return $res;




    }
}