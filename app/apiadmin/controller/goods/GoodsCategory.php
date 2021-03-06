<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/3
 * Time: 10:59
 */

namespace app\apiadmin\controller\goods;

use app\apiadmin\controller\Base;
use app\apiadmin\logic\GoodsLogic;
use app\apiadmin\validate\GoodsCategoryValidate;
use app\common\logic\HandleLogic;
use app\common\model\GoodsCategoryModel;
use think\facade\Db;

class GoodsCategory extends Base
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        if ($this->admin_user['identity'] != 1){
            echo json_encode(['status'=>'0','code'=>"10000",'msg'=>'没权限']);die;
        }
    }

    public function lists(GoodsCategoryModel $model)
    {
        $arr = array_return();
        $response = [
            'lists' => array()
        ];
        $lists = $model->getCatTreeList();
        $response['lists'] = $lists;
        $arr['data'] = $response;
        return return_json($arr);
    }

    public function info(GoodsCategoryModel $model,GoodsCategoryValidate $validate)
    {
        $arr = array_return();
        $response = [
            'info' => (object)array(),
        ];
        if (isset($this->param['id']) && !empty($this->param['id'])){
            $validate_result = $validate->scene('info')->check($this->param);
            if (!$validate_result) {
                $arr['status'] = 0;
                $arr['msg'] = $validate->getError();
                goto error;
            }
            $info = $model::find($this->param['id']);
            if (!$info){
                $arr['status'] = 0;
                $arr['msg'] = '信息不存在';
                goto error;
            }
            $response['info'] = turnString($info->toArray());
        }
        error:
        $arr['data'] = $response;
        return return_json($arr);
    }

    public function get_cat_tree_list(GoodsCategoryModel $goodsCategoryModel)
    {
        $arr = array_return();
        $level = $this->param['level'] ?? 4;
        $cat_list = $goodsCategoryModel->getCatTreeList($level);
        $arr['data'] = ['cat_list'=>$cat_list];
        return return_json($arr);
    }

    public function add()
    {
        return $this->handle('add');
    }

    public function edit()
    {
        return $this->handle('edit');
    }
    public function del()
    {
        return $this->handle('del');
    }

    public function handle($act='')
    {
        $handleLogic = new HandleLogic;
        $validate = new GoodsCategoryValidate;
        $GoodsLogic = new GoodsLogic();
        $arr = array_return();
        $validate_result = $validate->scene($act)->check($this->param);
        if (!$validate_result) {
            $arr['status'] = 0;
            $arr['msg'] = $validate->getError();
            goto error;
        }
        if ($act == 'del'){
            $this->param['is_del'] = 1;
            $act = 'edit';
        }
        $arr = $handleLogic->Handle($this->param,'GoodsCategoryModel',$act);
        if ($act != 'del'){
            $GoodsLogic->refreshCat($arr['object_id']);
        }
        error:
        return return_json($arr);
    }
}