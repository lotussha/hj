<?php


namespace app\common\model\config;


use app\common\model\CommonModel;

//短信模板
class ShortMessageModelConfigModel extends CommonModel
{
    protected $name = 'short_message_model_config';

    /**
     * 短信场景
     * User: hao
     * Date: 2020-08-10
     * @return \think\model\relation\hasOne
     */
    public function sceneConfig()
    {
        return $this->hasOne('app\common\model\config\ShortMessageSceneConfigModel', 'id', 'scene_id')->field('id,name');
    }

    /**
     * 获取短信场景列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * User: hao
     * Date: 2020/8/10
     */
//    public function getAllConfig($page=1,$list_row=10,$where=[],$field=true, $order = 'id desc'){
    public function getAllConfig($receive)
    {

        $receive['list_rows'] = isset($receive['list_rows']) ? $receive['list_rows'] : 10;  //多少条
        $receive['field'] = isset($receive['field']) ? $receive['field'] : true;//指定字段
        $receive['where'] = isset($receive['where']) ? $receive['where'] : '';//指定字段

        $data = $this->with(['sceneConfig'])
            ->field($receive['field'])
            ->where($receive['where'])
            ->where('is_delete', '=', '0')
            ->scope('where', $receive)
            ->paginate($receive['list_rows']);
        foreach ($data as $k => $v) {
            $data[$k]['scene_name'] = $v->sceneConfig['name'];
        }
        return $data->toArray();
    }


    /**
     * 获取详情
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * User: hao
     * Date: 2020/8/5
     */
    public function getInfoConfig($where = [], $field = '*')
    {
        $data = $this->with(['sceneConfig' => function ($query) {
            $query->field('name');
        }])
            ->field($field)
            ->where($where)
            ->find();

        if ($data) {
            $data['scene_name'] = $data->sceneConfig['name'];
            $data = $data->toArray();
        } else {
            $data = array();
        }
        return $data;
    }


}