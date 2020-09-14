<?php


namespace app\apiadmin\controller\order;


//订单评论
use app\apiadmin\controller\Base;
use app\common\model\order\OrderCommentModel;
use app\common\validate\order\OrderCommentValidate;
use app\apiadmin\logic\order\OrderCommentLogic;
use sakuno\utils\JsonUtils;
//评论
class OrderComment extends Base
{
    /**
     * 评论列表
     * User: hao
     * Date: 2020/8/8
     */
    public function index(OrderCommentModel $orderCommentModel){
        $data =$this->param;
        $data['list_rows'] = $this->admin['list_rows'];
        $data['field'] = 'id,uid,gid,order_id,spce_id,spec_list_id,content,img_url,create_time,sort,ip';
        $res = $orderCommentModel->getAllComment($data);
        return JsonUtils::successful('获取成功',$res);

    }

    /**
     * 评论详情
     * User: hao
     * Date: 2020/8/8
     */
    public function info(OrderCommentModel $orderCommentModel,OrderCommentValidate $validate){
        //检验
        $validate_result = $validate->scene('info')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),'00000');
        }
        //路由
        $field = 'id,uid,gid,order_id,spce_id,spec_list_id,content,img_url,create_time,sort,ip';
        $where = 'id='.$this->param['id'].' or groups='.$this->param['id'];
        $lists = $orderCommentModel->getInfoComment($where,$field);
        $arr['data'] = ['lists'=>$lists];
        return JsonUtils::successful('获取成功',$lists);
    }

    /**
     * 评论添加
     * User: hao
     * Date: 2020/8/8
     */
    public function add(OrderCommentModel $orderCommentModel,OrderCommentLogic $Logic){
        $data = $this->param;
        //检验
        $validate = new OrderCommentValidate;
        $validate_result = $validate->scene('add')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),'00000');
        }
        //事物
        $data = $Logic->addHandle($data);

        $arr = $orderCommentModel->addInfo($data);
        if ($arr){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败','00000');
        }
    }

    /**
     * 修改评论
     * User: hao
     * Date: 2020/8/8
     */
    public function edit(OrderCommentModel $orderCommentModel,OrderCommentLogic $Logic){
        $data = $this->param;
        //检验
        $validate = new OrderCommentValidate;
        $validate_result = $validate->scene('edit')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),'00000');
        }
        $data = $Logic->editHandle($data);
        $arr = $orderCommentModel->updateInfo(['id'=>$data['id']],$data);
        if ($arr){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败','00000');
        }
    }

    /**
     * 评论删除
     * User: hao
     * Date: 2020/8/8
     */
    public function del(OrderCommentModel $orderCommentModel,OrderCommentValidate $validate){
        $data = $this->param;
        $validate_result = $validate->scene('info')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        //路由
        $lists = $orderCommentModel->deleteInfo(['id'=>$this->param['id']]);
        if (!$lists){
            return JsonUtils::fail('操作失败');
        }
        return JsonUtils::successful('操作成功');
    }

    /**
     * 商家回复
     * User: hao
     * Date: 2020/8/8
     */
    public function reply(OrderCommentModel $orderCommentModel,OrderCommentValidate $validate,OrderCommentLogic $Logic){

        $data = $this->param;
        $validate_result = $validate->scene('reply')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $data = $Logic->replyHandle($data);
        $arr = $orderCommentModel->addInfo($data);
        if ($arr){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败','00000');
        }
    }

}