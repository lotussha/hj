<?php


namespace app\common\model\order;


use think\Model;

use think\model\concern\SoftDelete;
use app\common\model\CommonModel;

//订单评论
class OrderCommentModel extends CommonModel
{
//    use SoftDelete; // 一开启这个 软删除后，就没有数据了
//    protected $deleteTime = 'delete_time';
//    protected $defaultSoftDelete = 0;

    protected $name = 'order_comment';


    //可作为条件的字段
    protected $whereField = [
        'uid',
        'gid',
        'examine_is',
    ];


    /**
     * 用户
     * User: hao
     * Date: 2020-08-10
     * @return \think\model\relation\hasOne
     */
    public function user()
    {

        return $this->hasOne('app\common\model\user\UserModel', 'id', 'uid')->field('id,username,nick_name,avatar_url');
    }

    /**
     * 商品
     * User: hao
     * Date: 2020-08-10
     * @return \think\model\relation\hasOne
     */
    public function goods()
    {

        return $this->hasOne('app\common\model\GoodsModel', 'goods_id', 'gid')->field('goods_id,goods_name,original_img,shop_price,one_distribution_price');
    }


    /**
     * 获取列表  (关联商品和用户表)
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * User: hao
     * Date: 2020/8/10
     */
    public function getAllComment($receive)
    {

        $receive['list_rows'] = isset($receive['list_rows']) ? $receive['list_rows'] : 10;  //多少条
        $receive['field'] = isset($receive['field']) ? $receive['field'] : true;//指定字段
        $receive['where'] = isset($receive['where']) ? $receive['where'] : '';//指定字段


        $data = $this->with(['user', 'goods' => function ($query) {
            $query->where(['is_del' => '0']);
        }])
            ->where('is_delete', '<>', '1')
            ->where('groups', '=', '0')
            ->where($receive['where'])
            ->field($receive['field'])
            ->scope('where', $receive)
            ->paginate($receive['list_rows']);


        foreach ($data as $k => $v) {
            $data[$k]['nick_name'] = $v->user['nick_name'];
            $data[$k]['avatar_url'] = $v->user['avatar_url'];
            if ($data[$k]['img_url']) {
                $data[$k]['img_url'] = explode(',', $data[$k]['img_url']);
            }
        }

        return $data->toArray();
    }

    /**
     * 获取单个数据
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * User: hao
     * Date: 2020/8/10
     */
    public function getInfoComment($where, $field)
    {
        $data = $this->with(['goods'])
            ->field($field)
            ->where($where)
            ->order('create_time asc')
            ->select();
        foreach ($data as $k => $v) {
            $data[$k]['goods_name'] = $v->goods['goods_name'];
            $data[$k]['nick_name'] = $v->user['nick_name'];
        }
        return arrString($data->toArray());
    }

    //改变添加时间
    public function getCreateTimeAttr($v)
    {
        $v = date('Y-m-d H:i:s', $v);
        return $v;
    }

    /**
     * 更新数据
     * @param array $where
     * @param array $data
     * @param array $field_arr
     * @return CommonModel
     */
    final public function updateInfos($where = [], $data = [], $field_arr = [])
    {
        $data['update_time'] = time();
        $return_data = $this->allowField($field_arr)->where($where)->update($data);
        return $return_data;
    }

    //评论删除
//    public function CommentDel($where){
//        $res = $this->where($where)->update(['status' => 3, 'delete_time' => time()]);
//        return $res;
//    }
}