<?php


namespace app\common\model\settlement;

//入驻管理
use app\apiadmin\model\AdminUsers;
use app\common\model\CommonModel;
use app\common\model\GoodsModel;
use app\common\model\RegionModel;
use think\facade\Db;
use app\common\model\user\UserModel;

class SettlementModel extends CommonModel
{
    protected $name = 'settlement';

    //可搜索字段
    protected $searchField = [
        'nickname',
        'username',
        'phone',
    ];

    //可作为条件的字段
    protected $whereField = [
        'examine_is',
        'identity',
    ];


    /**
     * 用户
     * User: hao
     * Date: 2020-08-10
     * @return \think\model\relation\hasOne
     */
    public function user()
    {
        return $this->hasOne(UserModel::class, 'id', 'uid')->field('id,username,nick_name');
    }

    //入驻申请列表
    public function getAllSettlement($receive)
    {
        $receive['list_rows'] = isset($receive['list_rows']) ? $receive['list_rows'] : 10;  //多少条
        $receive['field'] = isset($receive['field']) ? $receive['field'] : '';//指定字段
        $data = $this->with(['user'])
            ->field($receive['field'])
            ->scope('where', $receive)
            ->append(['identity_text'])
            ->paginate($receive['list_rows']);
        foreach ($data as $k => $v) {
            $v['user_nick_name'] = $v->user['nick_name'];
            unset($data[$k]['user']);
            $list_id = array();
            array_push($list_id, $v['province'], $v['city'], $v['county'], $v['twon']);
            $list_id = implode(',', $list_id);
            $region = (new RegionModel())->where('id IN (' . $list_id . ')')->column('short_name', 'id');
            $v['province_name'] = $region[$v['province']];
            $v['city_name'] = $region[$v['city']];
            $v['county_name'] = $region[$v['county']];
            $v['twon_name'] = $region[$v['twon']];
            $data[$k] = $v;
        }

        return $data->toArray();
    }


    //入驻详情
    public function getInfoSettlement($where = [], $field = true)
    {
        $data = $this->with(['user'])
            ->append(['identity_text'])
            ->field($field)
            ->where($where)
            ->find();
        if ($data) {
            $data ['user_nick_name'] = $data->user['nick_name'];
            unset($data['user']);

            $list_id = array();
            array_push($list_id, $data['province'], $data['city'], $data['county'], $data['twon']);
            $list_id = implode(',', $list_id);
            $region = (new RegionModel())->where('id IN (' . $list_id . ')')->column('short_name', 'id');
            $data['province_name'] = $region[$data['province']];
            $data['city_name'] = $region[$data['city']];
            $data['county_name'] = $region[$data['county']];
            $data['twon_name'] = $region[$data['twon']];
            $data = $data->toArray();
        }
        return $data;
    }


    public function getIdentityTextAttr($value, $data)
    {
        return config('status')['IDENTITY'][$data['identity']] ?? '';
    }

    /*******************************************jomlz start******************************************************/
    /**
     * 订单详细收货地址
     * @param $value
     * @param $data
     * @return string
     */
    public function getFullAddressAttr($value, $data)
    {
        $province = Db::name('region')->where(['id' => $data['province']])->value('name');
        $city = Db::name('region')->where(['id' => $data['city']])->value('name');
        $county = Db::name('region')->where(['id' => $data['county']])->value('name');
        $twon = Db::name('region')->where(['id' => $data['twon']])->value('name');
        $address = $province . $city . $county . $twon . $data['address'];
        return $address;
    }

    //关联商品
    public function goods()
    {
        return $this->hasMany(GoodsModel::class, 'identity_id', 'admin_id');
    }

    public function getIdentityLists($page = 1, $list_row = 10, $where = [], $field = '', $order = 'sort desc', $param = [])
    {
        $lists = $this
            ->field($field)
            ->where(['examine_is' => 1])
            ->where($where)
            ->scope('where', $param)
            ->append(['full_address'])
            ->hidden(['province', 'city', 'county', 'twon', 'address'])
            ->page($page, $list_row)
            ->order($order)
            ->select()->toArray();
        return $lists;
    }
    /*******************************************jomlz end******************************************************/
}