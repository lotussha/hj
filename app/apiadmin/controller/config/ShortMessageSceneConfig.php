<?php


namespace app\apiadmin\controller\config;


use app\apiadmin\controller\Base;
use app\common\model\config\ShortMessageSceneConfigModel;
use app\common\validate\config\ShortMessageSceneConfigValidate;
use app\Request;
use sakuno\services\UtilService;
use sakuno\utils\JsonUtils;

//短信应用场景
class ShortMessageSceneConfig extends Base
{
    /**
     * 短信应用场景列表
     * @return array
     * @author hao    2020.08.18
     * */
    public function index(){

        $ShortMessageSceneConfigModel =new ShortMessageSceneConfigModel();
        $where = array();
        $where[]  = ['is_delete','<>','1'];
        $lists = $ShortMessageSceneConfigModel->getList($where,'id,name');
        return JsonUtils::successful('操作成功',$lists);
    }

    /**
     * 添加短信应用场景
     * @return array
     * @author hao    2020.08.18
     * */
    public function add(Request $request){

        //获取数据
        list($name) = UtilService::postMore([
            ['name', ''],
        ], $request, true);

        //检验
        $validate = new ShortMessageSceneConfigValidate();
        $validate_resule = $validate->scene('add')->check(['name' => $name]);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $ShortMessageSceneConfigModel =new ShortMessageSceneConfigModel();
        $res = $ShortMessageSceneConfigModel->addInfo(['name'=>$name]);
        if (!$res){
            return JsonUtils::fail('操作失败');
        }
        return JsonUtils::successful('操作成功');
    }

    /**
     * 修改短信应用场景
     * @return array
     * @author hao    2020.08.18
     * */
    public function edit(Request $request){

        //获取数据
        list($id,$name) = UtilService::postMore([
            ['id', ''],
            ['name', ''],
        ], $request, true);

        //检验
        $validate = new ShortMessageSceneConfigValidate();
        $validate_resule = $validate->scene('edit')->check(['name' => $name,'id' => $id]);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $ShortMessageSceneConfigModel =new ShortMessageSceneConfigModel();
        $res = $ShortMessageSceneConfigModel->updateInfo(['id'=>$id],['name'=>$name]);
        if (!$res){
            return JsonUtils::fail('操作失败');
        }
        return JsonUtils::successful('操作成功');
    }

    /**
     * 删除短信应用场景
     * @return array
     * @author hao    2020.08.18
     * */
    public function del(Request $request){
        //获取数据
        list($id) = UtilService::postMore([
            ['id', ''],
        ], $request, true);

        //检验
        $validate = new ShortMessageSceneConfigValidate();
        $validate_resule = $validate->scene('del')->check(['id' => $id]);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $ShortMessageSceneConfigModel =new ShortMessageSceneConfigModel();
        $res = $ShortMessageSceneConfigModel->deleteInfo(['id'=>$id]);
        if (!$res){
            return JsonUtils::fail('操作失败');
        }
        return JsonUtils::successful('操作成功');
    }

}