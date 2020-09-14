<?php


namespace app\apiadmin\controller\material;


use app\common\logic\material\ArticleTypeLogic;
use app\common\model\material\ArticleTypeModel;
use sakuno\utils\JsonUtils;
use think\Collection;
use app\common\model\material\ArticleModel;
use app\common\validate\material\ArticleValidate;
use app\common\validate\material\ArticleTypeValidate;
use app\apiadmin\controller\Base;

//文章

class Article extends Base
{
    /**
     * 文章列表
     * User: hao
     * Date: 2020/8/8
     */
    public function index( ArticleModel $ArticleModel){
        $data = $this->param;
        $data['field'] = 'id,title,img_url,type_id,status,content,sort,read_num,collect_num,author';
        $data['list_row'] = $this->admin['list_rows'];
        $list = $ArticleModel->getAllArticle($data);
        return JsonUtils::successful('获取成功',$list);
    }

    /**
     * 文章详情
     * User: hao
     * Date: 2020/8/8
     */
    public function article_info(ArticleModel $ArticleModel){
        //检验
        $validate = new ArticleValidate;
        $validate_result = $validate->scene('info')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),'00000');
        }
        //路由
        $lists = $ArticleModel->getInfoArticle(['id'=>$this->param['id']]);
        return JsonUtils::successful('获取成功',$lists);
    }

    /**
     * 文章添加
     * User: hao
     * Date: 2020/8/8
     */
    public function article_add(ArticleModel $ArticleModel){

        $data = $this->param;
        //检验
        $validate = new ArticleValidate();
        $validate_result = $validate->scene('add')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),'00000');
        }

        $arr = $ArticleModel->addInfo($data);
        if ($arr){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败','00000');
        }
    }

    /**
     * 文章修改
     * User: hao
     * Date: 2020/8/8
     */
    public function article_edit(ArticleModel $ArticleModel){

        $data = $this->param;
        //检验
        $validate = new ArticleValidate();
        $validate_result = $validate->scene('edit')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),'00000');
        }

        //模型
        $arr =$ArticleModel->updateInfo(['id'=>$this->param['id']],$data);

        if ($arr){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败','00000');
        }
    }

    /**
     * 文章删除
     * User: hao
     * Date: 2020/8/8
     */
    public function article_del( ArticleModel $ArticleModel){

        //检验
        $validate = new ArticleValidate;
        $validate_result = $validate->scene('del')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail('操作失败','00000');
        }
        //路由
        $lists = $ArticleModel->deleteInfo(['id'=>$this->param['id']]);
        if (!$lists){
            return JsonUtils::fail('操作失败','00000');
        }
        return JsonUtils::successful('操作成功');
    }


    /**
     * 文章分类列表
     * User: hao
     * Date: 2020/8/8
     */
    public function type_list( ArticleTypeModel $ArticleTypeModel){
        $where = array();

        $list = $ArticleTypeModel->getTypleList($where);
        return JsonUtils::successful('获取成功',['list'=>$list]);
    }

    /**
     * 文章分类详情
     * User: hao
     * Date: 2020/8/8
     */
    public function type_info(ArticleTypeModel $ArticleTypeModel){

        //检验
        $validate = new ArticleTypeValidate;
        $validate_result = $validate->scene('info')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail('操作失败','00000');
        }
        //路由
        $lists = $ArticleTypeModel->getTypeInfo(['id'=>$this->param['id']]);

        return JsonUtils::successful('获取成功',$lists);
    }

    /**
     * 文章分类添加
     * User: hao
     * Date: 2020/8/8
     */
    public function type_add(ArticleTypeModel $ArticleTypeModel){

        $data = $this->param;
        //检验
        $validate = new ArticleTypeValidate();
        $validate_result = $validate->scene('add')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),'00000');
        }
        //过滤相同名称
        $logic = new ArticleTypeLogic();
        $data = $logic->Handle($data);
        if (!$data){
            return JsonUtils::fail('已有相同的名称','00000');
        }
        //模型
        $arr =$ArticleTypeModel->addInfo($data);

        if ($arr){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败','00000');
        }
    }

    /**
     * 文章分类修改
     * User: hao
     * Date: 2020/8/8
     */
    public function type_edit(ArticleTypeModel $ArticleTypeModel){

        $data = $this->param;
        //检验
        $validate = new ArticleTypeValidate();
        $validate_result = $validate->scene('edit')->check($data);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),'00000');
        }

        //过滤相同名称
        $logic = new ArticleTypeLogic();
        $data = $logic->Handle($data,'edit');
        if (!$data){
            return JsonUtils::fail('已有相同的名称','00000');
        }
        //模型
        $arr =$ArticleTypeModel->updateInfo(['id'=>$data['id']],$data);

        if ($arr){
            return JsonUtils::successful('操作成功');
        }else{
            return JsonUtils::fail('操作失败','00000');
        }

    }

    /**
     * 文章分类删除
     * User: hao
     * Date: 2020/8/8
     */
    public function type_del(ArticleTypeModel $ArticleTypeModel){
        //检验
        $validate = new ArticleTypeValidate;
        $validate_result = $validate->scene('del')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError(),'00000');
        }
        //路由
        $lists = $ArticleTypeModel->deleteInfo(['id'=>$this->param['id']]);
        if (!$lists){
            return JsonUtils::fail('操作失败','00000');
        }

        return JsonUtils::successful('操作成功');
    }

}