<?php


namespace app\apiadmin\controller\config;


use app\apiadmin\controller\Base;
use app\common\model\config\AppletsMenuModel;
use app\common\validate\config\AppletsMenuValidate;
use app\Request;
use sakuno\services\UtilService;
use sakuno\utils\JsonUtils;

//小程序菜单
class AppletsMenu extends Base
{
    /**
     * 小程序菜单列表
     * @return array
     * @author hao    2020.08.18
     * */
    public function index(){
        $data = $this->param;
        $model =new AppletsMenuModel();
        $data['field'] = 'id,name,img_url,status';
        $lists = $model->getCommonLists($data);
        return JsonUtils::successful('操作成功',$lists);
    }

    /**
     * 小程序菜单详情
     * @return array
     * @author hao    2020.08.18
     * */
    public function info(Request $request){
        //获取数据
        list($id) = UtilService::postMore([
            ['id', ''],
        ], $request, true);

        $validate = new AppletsMenuValidate();
        $validate_resule = $validate->scene('info')->check(['id' => $id]);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $model =new AppletsMenuModel();
        $lists = $model->findInfo(['id'=>$id],'id,name,img_url,status');
        return JsonUtils::successful('操作成功',$lists);
    }


    /**
     * 小程序菜单添加
     * @return array
     * @author hao    2020.08.18
     * */
    public function add(Request $request){
        //获取数据
        list($name,$img_url,$status) = UtilService::postMore([
            ['name', ''],
            ['img_url', ''],
            ['status', '1'],
        ], $request, true);

        $data = array();
        $data['name'] = $name;
        $data['img_url'] = $img_url;
        $data['status'] = $status;
        $validate = new AppletsMenuValidate();
        $validate_resule = $validate->scene('add')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $model =new  AppletsMenuModel();
        $res = $model->addInfo($data);
        if (!$res){
            return JsonUtils::fail('操作失败', PARAM_IS_INVALID);
        }
        return JsonUtils::successful('操作成功');
    }

    /**
     *小程序菜单修改
     * @return array
     * @author hao    2020.08.18
     * */
    public function edit(Request $request){
        //获取数据
        list($id,$name,$img_url,$status) = UtilService::postMore([
            ['id', ''],
            ['name', ''],
            ['img_url', ''],
            ['status', '1'],
        ], $request, true);

        $data = array();
        $data['id'] = $id;
        $data['name'] = $name;
        $data['img_url'] = $img_url;
        $data['status'] = $status;
        $validate = new AppletsMenuValidate();
        $validate_resule = $validate->scene('edit')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $model =new AppletsMenuModel();
        $res = $model->updateInfo(['id'=>$id],$data);
        if (!$res){
            return JsonUtils::fail('操作失败', PARAM_IS_INVALID);
        }
        return JsonUtils::successful('操作成功');
    }

    /**
     * 小程序菜单状态
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
        $validate = new AppletsMenuValidate();
        $validate_resule = $validate->scene('status')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $model =new AppletsMenuModel();
        $res = $model->updateInfo(['id'=>$id],$data);
        if (!$res){
            return JsonUtils::fail('操作失败', PARAM_IS_INVALID);
        }
        return JsonUtils::successful('操作成功');
    }


    /**
     * 小程序菜单删除
     * @return array
     * @author hao    2020.08.18
     * */
    public function del(Request $request){
        //获取数据
        list($id) = UtilService::postMore([
            ['id', ''],
        ], $request, true);

        $validate = new AppletsMenuValidate();
        $validate_resule = $validate->scene('del')->check(['id'=>$id]);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $model =new AppletsMenuModel();
        $res = $model->deleteInfo(['id'=>$id]);

        if (!$res){
            return JsonUtils::fail('操作失败', PARAM_IS_INVALID);
        }
        return JsonUtils::successful('操作成功');
    }
}