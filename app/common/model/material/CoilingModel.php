<?php


namespace app\common\model\material;

use app\common\model\CommonModel;

//一键发圈
class CoilingModel extends CommonModel
{
    protected $name = 'coiling';

    //可作为条件的字段
    protected $whereField = [
        'identity_id',
        'gid',
        'uid',
    ];
    //可搜索字段
    protected $searchField = [
        'copywriting',
        'title',
    ];

    /**
     * 商品
     * User: hao
     * Date: 2020-08-14
     * @return \think\model\relation\hasOne
     */
    public function goods(){
        return $this->hasOne('app\common\model\GoodsModel', 'goods_id', 'gid')->field('goods_id,goods_name,one_distribution_price,original_img,shop_price');
    }

    /**
     * 入驻身份
     * User: hao
     * Date: 2020-08-14
     * @return \think\model\relation\hasOne
     */
    public function settlement(){
        return $this->hasOne('app\common\model\settlement\SettlementModel', 'admin_id', 'identity_id')->field('admin_id,logo_img,nickname');
    }



    /**
     * 获取列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * User: hao
     * Date: 2020/8/12
     */
    public function getAllCoiling($receive)
    {
        $receive['list_row'] = isset($receive['list_row']) ? $receive['list_row'] : 10;  //多少条
        $receive['field'] = isset($receive['field']) ? $receive['field'] : '';//指定字段

        $data = $this->with(['goods'=>function($query){
            $query->where(['is_del'=>'0']);
        },'settlement'])
            ->where('is_delete', '<>', '1')
            ->field($receive['field'])
            ->scope('where', $receive)
            ->paginate($receive['list_row']);

        foreach ($data as $key => $value) {
            if ($value['img_url']) {
                $value['img_url'] = explode(',', $value['img_url']);
            }
            $value['logo_img'] = $value['settlement']['logo_img'];
            $value['nickname'] = $value['settlement']['nickname'];
            $value['now_time'] = format_date($value['create_time']);
            unset($value['settlement']);
            $data[$key] = $value;
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
     * Date: 2020/8/12
     */
    public function getInfoCoiling($where = [], $field = '')
    {
        $data = $this
            ->field($field)
            ->where($where)
            ->find();

        if ($data) {
            $data['create_time'] = date('Y-m-d H:i:s', $data['create_time']);
            $data = $data->toArray();
        } else {
            $data = array();
        }
        return $data;
    }
}