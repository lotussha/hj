<?php


namespace app\apiadmin\controller\config;


use app\apiadmin\controller\Base;
use app\common\model\config\ShortMessageModelConfigModel;
use app\common\validate\config\ShortMessageModelConfigValidate;
use app\Request;
use sakuno\services\UtilService;
use sakuno\utils\JsonUtils;

//短信模板
class ShortMessageModelConfig extends Base
{
    /**
     * 短信模板列表
     * @return array
     * @author hao    2020.08.18
     * */
    public function index(Request $request){
        //获取数据

        $data = $this->param;
        $data['field'] = 'id,scene_id,autograph,message_content';
        $ShortMessageModelConfigModel =new ShortMessageModelConfigModel();
        $list = $ShortMessageModelConfigModel->getAllConfig($data);
        return JsonUtils::successful('操作成功',$list);
    }

    /**
     * 短信模板详情
     * @return array
     * @author hao    2020.08.18
     * */
    public function info(Request $request){
        //获取数据
        list($id) = UtilService::postMore([
            ['id', ''],
        ], $request, true);

        //检验
        $validate = new ShortMessageModelConfigValidate();
        $validate_resule = $validate->scene('info')->check(['id' => $id]);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $ShortMessageModelConfigModel =new ShortMessageModelConfigModel();
        $where = array();
        $field = 'id,scene_id,autograph,message_content';
        $lists = $ShortMessageModelConfigModel->getInfoConfig($where,$field);

        return JsonUtils::successful('操作成功',$lists);
    }

    /**
     * 短信模板添加
     * @return array
     * @author hao    2020.08.18
     * */
    public function add(Request $request){
        //获取数据
        list($scene_id,$autograph,$message_model,$message_content) = UtilService::postMore([
            ['scene_id', ''],
            ['autograph', ''],
            ['message_model', ''],
            ['message_content', ''],
        ], $request, true);

        $data =array();
        $data['scene_id'] = $scene_id;
        $data['autograph'] = $autograph;
        $data['message_model'] = $message_model;
        $data['message_content'] = $message_content;
        //检验
        $validate = new ShortMessageModelConfigValidate();
        $validate_resule = $validate->scene('add')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $ShortMessageModelConfigModel =new ShortMessageModelConfigModel();
        $res = $ShortMessageModelConfigModel->addInfo($data);
        if (!$res){
            return JsonUtils::fail('操作失败');
        }
        return JsonUtils::successful('操作成功');
    }


    /**
     * 短信模板修改
     * @return array
     * @author hao    2020.08.18
     * */
    public function edit(Request $request){
        //获取数据
        list($id,$scene_id,$autograph,$message_model,$message_content) = UtilService::postMore([
            ['id', ''],
            ['scene_id', ''],
            ['autograph', ''],
            ['message_model', ''],
            ['message_content', ''],
        ], $request, true);

        $data =array();
        $data['id'] = $id;
        $data['scene_id'] = $scene_id;
        $data['autograph'] = $autograph;
        $data['message_model'] = $message_model;
        $data['message_content'] = $message_content;
        //检验
        $validate = new ShortMessageModelConfigValidate();
        $validate_resule = $validate->scene('edit')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $ShortMessageModelConfigModel =new ShortMessageModelConfigModel();
        $res = $ShortMessageModelConfigModel->updateInfo(['id'=>$id],$data);
        if (!$res){
            return JsonUtils::fail('操作失败');
        }
        return JsonUtils::successful('操作成功');
    }

    /**
     * 短信模板删除
     * @return array
     * @author hao    2020.08.18
     * */
    public function del(Request $request){
        //获取数据
        list($id) = UtilService::postMore([
            ['id', ''],
        ], $request, true);

        //检验
        $validate = new ShortMessageModelConfigValidate();
        $validate_resule = $validate->scene('del')->check(['id'=>$id]);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }

    }
}