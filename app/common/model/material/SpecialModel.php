<?php


namespace app\common\model\material;


use think\Model;
use think\model\concern\SoftDelete;
use app\common\model\CommonModel;

class SpecialModel extends CommonModel
{
//    use SoftDelete; // 一开启这个 软删除后，就没有数据了
//    protected $deleteTime = 'delete_time';
    protected $name = 'special';

    /**
     * 专题分类
     * User: hao
     * Date: 2020-08-10
     * @return \think\model\relation\hasOne
     */
    public function SpecialType() {
        return $this->hasOne('app\common\model\material\SpecialTypeModel', 'id', 'type_id')->field('id,name');
    }

    /**
     * 获取列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * User: hao
     * Date: 2020/8/5
     */
    public function getAllSpecial($page = 1, $list_row = 10, $order = 'sort desc,id desc', $where = [])
    {
        $data = $this->with(['SpecialType'=>function($query) {
            $query->field('name');
        }])
            ->field('id,title,img_url,type_id,cover,read_num,collect_num,status,content,sort')
            ->where($where)
            ->where('is_delete','<>',1)
            ->page($page, $list_row)
            ->order($order)
            ->select();
        foreach ($data as $k=>$v){
            $data[$k]['type_name'] = $v->SpecialType['name'];
        }
        return arrString($data->toArray());
    }


    /**
     * 获取专题详情
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * User: hao
     * Date: 2020/8/5
     */
    public function getInfoSpecial($where){

        $data = $this->with(['SpecialType'=>function($query) {
            $query->field('name');
        }])
            ->field('id,title,img_url,type_id,status,content,sort,cover,read_num,collect_num')
            ->where($where)
            ->find();
        $data['type_name'] = $data->SpecialType['name'];

        if ($data){
            $data = $data->toArray();
        }else{
            $data = array();
        }
        return $data;
    }

}