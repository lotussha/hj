<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/26
 * Time: 10:52
 */

namespace app\apiadmin\controller\goods;

use app\apiadmin\controller\Base;
use app\common\logic\HandleLogic;
use app\common\model\GoodsServiceModel;
use app\common\validate\goods\GoodsServiceValidate;
use sakuno\utils\JsonUtils;

class GoodsService extends Base
{
    public function lists()
    {
        $lists = (new GoodsServiceModel())->where(['is_del'=>0])->hidden(['add_time','is_del'])->append(['add_time_date'])->select()->toArray();
        $data = ['lists'=>$lists];
        return JsonUtils::successful('获取成功',$data);
    }

    public function info(GoodsServiceValidate $validate,GoodsServiceModel $model)
    {
        $validate_result = $validate->scene('info')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $info = $model::find($this->param['id']);
        if (!$info){
            return JsonUtils::fail('信息不存在');
        }
        $response['info'] = turnString($info->toArray());
        return JsonUtils::successful('获取成功',$response);
    }

    public function add()
    {
        return $this->handle('add',$this->param);
    }

    public function edit()
    {
        return $this->handle('edit',$this->param);
    }
    public function del()
    {
        return $this->handle('del',$this->param);
    }

    public function handle($act,$param)
    {
        $handleLogic = new HandleLogic();
        $validate = new GoodsServiceValidate();
        $validate_result = $validate->scene($act)->check($param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        if ($act=='del'){
            $param['is_del'] = 1;
            $act = 'edit';
        }
        $arr = $handleLogic->Handle($param,'GoodsServiceModel',$act);
        return return_json($arr);
    }
}