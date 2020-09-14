<?php


namespace app\apiadmin\controller\material;

use app\apiadmin\controller\Base;
use app\common\model\material\NewsModel;
use app\common\validate\material\NewsValidate;
use sakuno\utils\JsonUtils;

//消息
class News extends Base
{

    /**
     * 消息列表
     * User: hao
     * Date: 2020.09.02
     */
    public function index(){
        $data = $this->param;
        $data['field'] = 'id,title,content,is_show';
        $list = (new NewsModel())->getAllNews($data);
        return JsonUtils::successful('操作成功',$list);
    }

    /**
     * 消息详情
     * User: hao
     * Date: 2020.09.02
     */
    public function info(){
        $data = $this->param;
        $validate = new NewsValidate();
        $validate_result = $validate->scene('info')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $data['field'] = 'id,title,content,is_show';
        $list =(new NewsModel())->getInfoNews($data);
        return JsonUtils::successful('操作成功',$list);
    }


    /**
     * 消息添加
     * User: hao
     * Date: 2020.09.02
     */
    public function add(){
        $data = $this->param;
        $validate = new NewsValidate();
        $validate_result = $validate->scene('add')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }

        $res = (new NewsModel())->addInfo($data);
        if (!$res){
            return JsonUtils::fail('添加失败');
        }
        return JsonUtils::successful('操作成功');
    }


    /**
     * 消息修改
     * User: hao
     * Date: 2020.09.02
     */
    public function edit(){
        $data = $this->param;
        $validate = new NewsValidate();
        $validate_result = $validate->scene('edit')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $res = (new NewsModel())->updateInfo(['id'=>$data['id']],$data);
        if (!$res){
            return JsonUtils::fail('修改失败');
        }
        return JsonUtils::successful('操作成功');
    }


    /**
     * 信息删除
     * User: hao
     * Date: 2020.09.02
     */
    public function del(){
        $data = $this->param;
        $validate = new NewsValidate();
        $validate_result = $validate->scene('del')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $res = (new NewsModel())->deleteInfo(['id'=>$data['id']]);
        if (!$res){
            return JsonUtils::fail('删除失败');
        }
        return JsonUtils::successful('操作成功');
    }
}