<?php


namespace app\apiadmin\controller\config;


use app\apiadmin\controller\Base;
use app\common\model\config\ShortMessageInterfaceConfigModel;
use app\common\validate\config\ShortMessageInterfaceConfigValidate;
use app\Request;
use sakuno\services\UtilService;
use sakuno\utils\JsonUtils;

//短信接口
class ShortMessageInterfaceConfig extends Base
{
    /**
     * 短信接口列表
     * @return array
     * @author hao    2020.08.18
     * */
    public function index()
    {
        $model = new ShortMessageInterfaceConfigModel();
        $data = $this->param;

        $data['field'] = 'id,appkey,secretkey,name';
        $lists = $model->getCommonLists($data);

        return JsonUtils::successful('操作成功', $lists);
    }


    /**
     * 短信接口详情
     * @return array
     * @author hao    2020.08.18
     * */
    public function info(Request $request)
    {
        //获取数据
        list($id) = UtilService::postMore([
            ['id', ''],
        ], $request, true);

        $validate = new ShortMessageInterfaceConfigValidate();
        $validate_resule = $validate->scene('info')->check(['id' => $id]);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }

        $model = new ShortMessageInterfaceConfigModel();
        $lists = $model->findInfo(['id' => $id], 'id,appkey,secretkey,name,stutas');
        return JsonUtils::successful('操作成功', $lists);
    }


    /**
     * 短信接口添加
     * @return array
     * @author hao    2020.08.18
     * */
    public function add(Request $request)
    {
        //获取数据
        list($name, $appkey, $secretkey, $stutas) = UtilService::postMore([
            ['name', ''],
            ['appkey', ''],
            ['secretkey', ''],
            ['stutas', ''],
        ], $request, true);

        $data = array();
        $data['name'] = $name;
        $data['appkey'] = $appkey;
        $data['secretkey'] = $secretkey;
        $data['stutas'] = $stutas;
        $validate = new ShortMessageInterfaceConfigValidate();
        $validate_resule = $validate->scene('add')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $model = new ShortMessageInterfaceConfigModel();
        $res = $model->addInfo($data);
        if (!$res) {
            return JsonUtils::fail('操作失败');
        }
        return JsonUtils::successful('操作成功');
    }

    /**
     * 短信接口修改
     * @return array
     * @author hao    2020.08.18
     * */
    public function edit(Request $request)
    {
        //获取数据
        list($id, $name, $appkey, $secretkey, $stutas) = UtilService::postMore([
            ['id', ''],
            ['name', ''],
            ['appkey', ''],
            ['secretkey', ''],
            ['stutas', ''],
        ], $request, true);

        $data = array();
        $data['id'] = $id;
        $data['name'] = $name;
        $data['appkey'] = $appkey;
        $data['secretkey'] = $secretkey;
        $data['stutas'] = $stutas;
        $validate = new ShortMessageInterfaceConfigValidate();
        $validate_resule = $validate->scene('edit')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }
        $model = new ShortMessageInterfaceConfigModel();
        $res = $model->updateInfo(['id' => $id], $data);
        if (!$res) {
            return JsonUtils::fail('操作失败');
        }
        return JsonUtils::successful('操作成功');
    }


    /**
     * 短信接口删除
     * @return array
     * @author hao    2020.08.18
     * */
    public function del(Request $request)
    {
        //获取数据
        list($id) = UtilService::postMore([
            ['id', ''],
        ], $request, true);

        $validate = new ShortMessageInterfaceConfigValidate();
        $validate_resule = $validate->scene('del')->check(['id' => $id]);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }

        $model = new ShortMessageInterfaceConfigModel();
        $res = $model->deleteInfo(['id' => $id]);
        if (!$res) {
            return JsonUtils::fail('操作失败');
        }
        return JsonUtils::successful('操作成功');
    }

}