<?php


namespace app\common\model\user;

use app\common\logic\user\UserAccountLogLogic;
use app\common\logic\user\UserMethodLogic;
use app\common\model\CommonModel;

use think\facade\Db;


//用户模型
class UserModel extends CommonModel
{
    protected $name = 'user';

    //可搜索字段
    protected $searchField = [
        'username',
        'nick_name',
    ];

    //可作为条件的字段
    protected $whereField = [
        'status',
        'grade_id',
        'id'
    ];

    //可字段搜索器 时间范围查询
    protected $timeField = [
        'create_time',
    ];

    /**
     * 身份等级
     * User: hao
     * Date: 2020-08-14
     * @return \think\model\relation\hasOne
     */
    public function UserGrade()
    {
        return $this->hasOne('UserGradeModel', 'id', 'grade_id')->field('id,name');
    }

    /**
     * 用户详情
     * User: hao
     * Date: 2020-08-14
     * @return \think\model\relation\hasOne
     */
    public function UserDetails()
    {
        return $this->hasOne('UserDetailsModel', 'uid', 'id');
    }

    /**
     * 上级信息
     * User: hao
     * Date: 2020-08-14
     * @return \think\model\relation\hasOne
     */
    public function UserShare()
    {
        return $this->hasOne('UserModel', 'id', 'share_id');
    }



    public function getNickNameAttr($v)
    {
        return EmojiDecode($v);
    }

    /**
     * 获取列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * User: hao
     * Date: 2020/8/14
     */
    public function getAllUser($receive)
    {
        $receive['list_rows'] = isset($receive['list_rows']) ? $receive['list_rows'] : 10;  //多少条
        $receive['field'] = isset($receive['field']) ? $receive['field'] : true;//指定字段
        $receive['where'] = isset($receive['where']) ? $receive['where'] : '';//指定字段


        $data = $this
            ->with(['UserGrade' => function ($query) {
                $query->field('name');
            }, 'UserDetails' => function ($query) {
                $query->field(true);
            },'UserShare'=>function($query){
                $query->field('id,username,nick_name');
            }])
            ->field($receive['field'])
            ->where($receive['where'])
            ->hidden(['password'])
            ->append(['id_num'])
            ->scope('where', $receive)
            ->paginate($receive['list_rows']);

        foreach ($data as $k => $v) {
            $v['grade_name'] = $v->UserGrade['name'];
            $v['share_username'] = $v->UserShare['username'];
            $v['share_nick_name'] = $v->UserShare['nick_name'];
            $UserDetails = json_decode($v->UserDetails, true);
            $v = json_decode($v, true);
            unset($v['UserGrade']);
            unset($v['UserDetails']);
            unset($v['UserShare']);
            $v = array_merge($v, $UserDetails);
            $data[$k] = $v;
        }
        return $data->toArray();
    }

    /**
     * 获取全部id和上级
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * User: hao
     * Date: 2020/8/14
     */
    public function getAddId()
    {
        $data = $this->field('id,share_id')->select();
        return arrString($data->toArray());
    }


    /**
     * 获取用户详情
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * User: hao
     * Date: 2020/8/14
     */
    public function getInfoUser($where = [], $field = '*', $hidden = ['password'])
    {
        $data = $this->with(['UserGrade' => function ($query) {
            $query->field('name');
        }, 'UserDetails','UserShare'=>function($query){
            $query->field('id,username,nick_name');
        }])
            ->field($field)
            ->append(['id_num'])
            ->hidden($hidden)
            ->where($where)
            ->find();

        if ($data) {
            $data['grade_name'] = $data->UserGrade['name'];
            $data['share_username'] = $data->UserShare['username'];
            $data['share_nick_name'] = $data->UserShare['nick_name'];
            $UserDetails = json_decode($data->UserDetails, true);
            $data = $data->toArray();
            $data = array_merge($data, $UserDetails);
            unset($data['UserDetails']);
            unset($data['UserGrade']);
            unset($data['UserShare']);
        } else {
            $data = array();
        }
        return $data;
    }


    /**
     * @param $id
     * @param int $buy_user_id
     * @param int $good_price
     * @param array $order_goods
     * User: Jomlz
     */
    public function distribution($buy_user_id=0,$order_goods=[])
    {
        $buy_user_info = $this->with('UserGrade')->find($buy_user_id)->toArray();
        //分销记录
        if ($buy_user_info && $buy_user_info['share_id'] > 0 && $order_goods['one_distribution_price'] > 0)
        {
            //一级分销用户
            $one_user_info = $this->with(['UserGrade','UserDetails'])->find($buy_user_info['share_id'])->toArray();
            $one_commission = [
                'uid' => $one_user_info['id'],
                'pid' => $buy_user_info['id'],
                'money' => $order_goods['one_distribution_price'],
                'goods_id' => $order_goods['goods_id'],
                'order_id' => $order_goods['order_id'],
                'order_sn' => $order_goods['goods_sn'],
                'create_time' => time(),
                'commission_status' => 2,
                'type' => 102,
                'remark' => '下单获取一级分销金额',
                'original_money' => $one_user_info['UserDetails']['commission_money'],
                'original_frozen_money' => $one_user_info['UserDetails']['commission_frozen_money'],
                'now_money' => $one_user_info['UserDetails']['commission_money'],
                'now_frozen_money' => $one_user_info['UserDetails']['commission_frozen_money'] + $order_goods['one_distribution_price'],
                'distribution_level' => 1,
                'uid_grade' => $buy_user_info['grade_id'],
                'pid_grade' => $one_user_info['grade_id'],
            ];
//            (new UserCommissionLogModel())->allowField(true)->save($one_commission);
//            Db::name('user_details')->where(['uid'=>$one_user_info['id']])->inc('commission_frozen_money',$order_goods['one_distribution_price'])->update();
            (new UserAccountLogLogic())::userAccountLogAdd($one_user_info['id'],6,$one_commission,'下单获取一级分销金额');
            if ($one_user_info && $one_user_info['share_id'] > 0){
                //二级分销用户
                $two_user_info = $this->with(['UserGrade','UserDetails'])->field('id,nick_name,grade_id,share_id')->find($one_user_info['share_id'])->toArray();
                $two_commission = [
                    'uid' => $two_user_info['id'],
                    'pid' => $buy_user_info['id'],
                    'money' => $order_goods['two_distribution_price'],
                    'goods_id' => $order_goods['goods_id'],
                    'order_sn' => $order_goods['goods_sn'],
                    'create_time' => time(),
                    'commission_status' => 2,
                    'type' => 102,
                    'remark' => '下单获取二级分销金额',
                    'original_money' => $two_user_info['UserDetails']['commission_money'],
                    'original_frozen_money' => $two_user_info['UserDetails']['commission_frozen_money'],
                    'now_money' => $two_user_info['UserDetails']['commission_money'],
                    'now_frozen_money' => $two_user_info['UserDetails']['commission_frozen_money'] + $order_goods['two_distribution_price'],
                    'distribution_level' => 2,
                    'uid_grade' => $buy_user_info['grade_id'],
                    'pid_grade' => $two_user_info['grade_id'],
                ];
//                Db::name('user_commission_log')->insert($two_commission);
//                Db::name('user_details')->where(['uid'=>$two_user_info['id']])->inc('commission_frozen_money',$order_goods['two_distribution_price'])->update();
            }
        }else{
            //如果没上级用，分销佣金归为平台
        }
    }


    /**
     * 我的团队
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * User: hao
     * Date: 2020.09.02
     */
    public function getUserTeam($where, $receive)
    {
        $receive['list_rows'] = isset($receive['list_rows']) ? $receive['list_rows'] : 10;  //多少条

        $data = $this
            ->with(['UserGrade' => function ($query) {
                $query->field('name');
            }, 'UserDetails' => function ($query) {
                $query->field(['uid,sum_money,province,city']);
            }])
            ->field('id,grade_id,nick_name,avatar_url,share_id,last_time')
            ->where($where)
            ->append(['id_num', 'share_id_num'])
            ->scope('where', $receive)
            ->paginate($receive['list_rows']);

        foreach ($data as $k => $v) {
            $v['grade_name'] = $v->UserGrade['name'];
            $UserDetails = json_decode($v->UserDetails, true);
            $v = json_decode($v, true);
            unset($v['UserGrade']);
            unset($v['UserDetails']);
            $v = array_merge($v, $UserDetails);
            if ($v['share_id']==$receive['uid']){
                $v['level']= 1;
            }else{
                $v['level'] =2;
            }
            $v['last_time'] = date('Y-m-d H:i:s',$v['last_time']);
            $data[$k] = $v;
        }
        return $data->toArray();
    }


    /**
     * 用户虚拟ID
     * User: hao
     * Date: 2020.9.2
     * */
    public function fictitiousId($id)
    {
        return 263 * 165 + $id * 13;
    }

    public function getShareIdNumAttr($value, $data)
    {
        return 263 * 165 + $data['share_id'] * 13;
    }

    public function getIdNumAttr($value, $data)
    {
        return 263 * 165 + $data['id'] * 13;
    }



}