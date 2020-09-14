<?php


namespace app\common\model\material;

use think\model\concern\SoftDelete;
use app\common\model\CommonModel;

class ArticleModel extends CommonModel
{
//    use SoftDelete; // 一开启这个 软删除后，就没有数据了
//    protected $deleteTime = 'delete_time';
    protected $name = 'article';

    //可作为条件的字段
    protected $whereField = [
        'type_id',
        'is_hot',

    ];

    //可搜索字段
    protected $searchField = [
        'title',
        'author',
//        'content',
    ];

    /**
     * 文章分类
     * User: hao
     * Date: 2020-08-10
     * @return \think\model\relation\hasOne
     */
    public function ArticleType() {
        return $this->hasOne('app\common\model\material\ArticleTypeModel', 'id', 'type_id')->field('id,name');
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
    public function getAllArticle($receive)
    {
        $receive['list_rows'] = isset($receive['list_rows'])?$receive['list_rows']:10;  //多少条
        $receive['field'] = isset($receive['field'])?$receive['field']:'';//指定字段

        $data = $this->with(['ArticleType'=>function($query) {
            $query->field('name')->where('is_delete','<>','1');
        }])
            ->field($receive['field'])
            ->where('is_delete','<>',1)
            ->scope('where', $receive)
            ->paginate($receive['list_rows']);
        foreach ($data as $k=>$v){
            $data[$k]['type_name'] = $v->ArticleType['name'];
            $data[$k]['now_time'] = format_date($data[$k]['create_time']);
            $data[$k]['img_url'] = explode(',',$data[$k]['img_url']);
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
    public function getInfoArticle($where){
        $data = $this->with(['ArticleType'=>function($query) {
            $query->field('name');
        }])
            ->field('id,title,img_url,type_id,status,content,sort,read_num,collect_num')
            ->where($where)
            ->find();
        $data['type_name'] = $data->ArticleType['name'];
        $data['img_url'] = explode(',',$data['img_url']);
        if ($data){
            $data = $data->toArray();
        }else{
            $data = array();
        }
        return $data;
    }
}