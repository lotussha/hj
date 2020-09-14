<?php


namespace app\common\model\material;


use app\common\model\CommonModel;

//消息
class NewsModel extends CommonModel
{
    protected $name = 'news';

    //可作为条件的字段
    protected $whereField = [
        'is_show',
    ];

    //可搜索字段
    protected $searchField = [
        'title',
        'content',
    ];

    /**
     * 获取列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * User: hao
     * Date: 2020/8/5
     */
    public function getAllNews($receive)
    {
        $receive['list_rows'] = isset($receive['list_rows']) ? $receive['list_rows'] : 10;  //多少条
        $receive['field'] = isset($receive['field']) ? $receive['field'] : true;//指定字段
        $receive['where'] = isset($receive['where']) ? $receive['where'] : '';//指定字段
        $data = $this
            ->field($receive['field'])
            ->where('is_delete', '<>', 1)
            ->where($receive['where'])
            ->scope('where', $receive)
            ->paginate($receive['list_rows']);
        return $data->toArray();
    }

    /**
     * 获取详情
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * User: hao
     * Date: 2020.09.02
     */
    public function getInfoNews($receive){
        $where = array();
        $where['id'] = $receive['id']??0;
        $data = $this
            ->field($receive['field'])
            ->where($where)
            ->find();
        if ($data){
            $data = $data->toArray();
        }else{
            $data = array();
        }
        return $data;
    }

}