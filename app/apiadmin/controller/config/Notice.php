<?php


namespace app\apiadmin\controller\config;

//公告
use app\apiadmin\controller\Base;
use app\common\model\config\NoticeModel;
use app\common\model\config\RechargeOptionModel;
use app\common\validate\config\NoticeValidate;
use app\common\validate\config\RechargeOptionValidate;
use app\Request;
use sakuno\services\UtilService;
use sakuno\utils\JsonUtils;

class Notice extends Base
{
    /**
     * 公告列表
     * @return array
     * @author hao    2020.08.18
     * */
    public function index(){
        $model =new NoticeModel();
        $where = array();
        $where[]  = ['is_delete','<>','1'];
        $lists = $model->getList($where,'id,title,status');
        return JsonUtils::successful('操作成功',$lists);
    }

    /**
     * 公告详情
     * @return array
     * @author hao    2020.08.18
     * */
    public function info(Request $request){
        //获取数据
        list($id) = UtilService::postMore([
            ['id', ''],
        ], $request, true);

        $validate = new NoticeValidate();
        $validate_resule = $validate->scene('info')->check(['id' => $id]);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $model =new NoticeModel();
        $lists = $model->findInfo(['id'=>$id],'id,title,status,content');
        return JsonUtils::successful('操作成功',$lists);
    }


    /**
     * 公告添加
     * @return array
     * @author hao    2020.08.18
     * */
    public function add(Request $request){
        //获取数据
        list($title,$content,$status) = UtilService::postMore([
            ['title', ''],
            ['content', ''],
            ['status', '1'],
        ], $request, true);

        $data = array();
        $data['title'] = $title;
        $data['content'] = $content;
        $data['status'] = $status;
        $validate = new NoticeValidate();
        $validate_resule = $validate->scene('add')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $model =new NoticeModel();
        $res = $model->addInfo($data);
        if (!$res){
            return JsonUtils::fail('操作失败', PARAM_IS_INVALID);
        }
        return JsonUtils::successful('操作成功');
    }

    /**
     * 公告修改
     * @return array
     * @author hao    2020.08.18
     * */
    public function edit(Request $request){
        //获取数据
        list($id,$title,$content,$status) = UtilService::postMore([
            ['id', ''],
            ['title', ''],
            ['content', ''],
            ['status', '1'],
        ], $request, true);

        $data = array();
        $data['id'] = $id;
        $data['title'] = $title;
        $data['content'] = $content;
        $data['status'] = $status;
        $validate = new NoticeValidate();
        $validate_resule = $validate->scene('edit')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $model =new NoticeModel();
        $res = $model->updateInfo(['id'=>$id],$data);
        if (!$res){
            return JsonUtils::fail('操作失败', PARAM_IS_INVALID);
        }
        return JsonUtils::successful('操作成功');
    }

    /**
     * 公告状态
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
        $validate = new NoticeValidate();
        $validate_resule = $validate->scene('status')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $model =new NoticeModel();
        $res = $model->updateInfo(['id'=>$id],$data);
        if (!$res){
            return JsonUtils::fail('操作失败', PARAM_IS_INVALID);
        }
        return JsonUtils::successful('操作成功');
    }


    /**
     * 公告删除
     * @return array
     * @author hao    2020.08.18
     * */
    public function del(Request $request){
        //获取数据
        list($id) = UtilService::postMore([
            ['id', ''],
        ], $request, true);

        $validate = new NoticeValidate();
        $validate_resule = $validate->scene('del')->check(['id'=>$id]);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $model =new NoticeModel();
        $res = $model->deleteInfo(['id'=>$id]);

        if (!$res){
            return JsonUtils::fail('操作失败', PARAM_IS_INVALID);
        }
        return JsonUtils::successful('操作成功');
    }
}