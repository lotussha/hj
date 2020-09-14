<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/1
 * Time: 9:53
 */

namespace app\apiadmin\model;

use app\common\model\settlement\SettlementModel;
use think\facade\Cache;

/**
 * @property int id
 * @property mixed sign_str
 * @property mixed auth_url
 * @property mixed role
 * @property string password
 */
class AdminUsers extends Model
{
    protected $name = 'admin_users';

    public $noDeletionId = [
        1, 2
    ];

    //可搜索字段
    protected $searchField = [
        'username',
        'nickname',
    ];

    //可作为条件的字段
    protected $whereField = [
        'status',
        'identity',
        'role',
    ];


    //给默认菜单
    public static $default_url = '';

    //模型初始化
    public static function init()
    {
    }

    public static function onBeforeInsert($data)
    {
        $data->password = password_hash($data->password, 1);
    }

    public static function onBeforeUpdate($data)
    {
//        Cache::store('user_menu')->delete('user_menu'.$data->id);
        $old = (new static())::find($data->id);
        if ($data->password !== $old->password) {
            $data->password = password_hash($data->password, 1);
        }
        $admin_user = self::where('id !='.$data->id)->column('username');
        if (in_array($data->username,$admin_user)){
            exception('用户名已存在');
        }
    }

    public static function onAfterDelete($data)
    {
        Cache::store('user_menu')->delete('user_menu'.$data->id);
    }

    //角色获取器
    protected function getRoleAttr($value)
    {
        return explode(',', $value);
    }

    //用户角色名称
    protected function getRoleTextAttr($value, $data)
    {
        return AdminRoleModel::where('id', 'in', $data['role'])->column('id,name', 'id');
    }

    /**
     * 获取已授权url
     * @param $value
     * @param $data
     * @return array
     */
    protected function getAuthUrlAttr($value, $data)
    {
        $role_urls  = AdminRoleModel::where('id', 'in', $data['role'])->where('status', 1)->column('url');
        $url_id_str = '';
        foreach ($role_urls as $key => $val) {
            $url_id_str .= $key == 0 ? $val : ',' . $val;
        }
        $url_id_str = $url_id_str.','.self::$default_url;
        $url_id   = array_unique(explode(',', $url_id_str));
        $auth_url = [];
        if (count($url_id) > 0) {
            $auth_url = AdminMenuModel::where('id', 'in', $url_id)->column('url');
        }
        return $auth_url;
    }

    public function getShowMenu($admin_user = '')
    {
        if ($admin_user->id == 1) {
            return AdminMenuModel::where('is_show', 1)->order('sort_id', 'asc')->order('id', 'asc')->column('id,parent_id,name,url,icon,sort_id', 'id');
        }

        $role_urls = AdminRoleModel::where('id', 'in', $admin_user->role)->where('status', 1)->column('url');

        $url_id_str = self::$default_url;

        foreach ($role_urls as $key => $val) {
            $url_id_str .= $key == 0 ? $val : ',' . $val;
        }
        $url_id = array_unique(explode(',', $url_id_str));

        return AdminMenuModel::where('id', 'in', $url_id)->where('is_show', 1)->order('sort_id', 'asc')->order('id', 'asc')->column('id,parent_id,name,url,icon,sort_id', 'id');
    }

    public function identityInfo()
    {
        return $this->hasOne(SettlementModel::class,'id','s_id');
    }

    /**
     * 获取列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * User: hao
     * Date: 2020.09.05
     */
    public function getAllAdmin($receive){
        $receive['list_rows'] = isset($receive['list_rows']) ? $receive['list_rows'] : 10;  //多少条
        $receive['field'] = isset($receive['field']) ? $receive['field'] : true;//指定字段
        $receive['where'] = isset($receive['where']) ? $receive['where'] : '';//指定字段

        $data = $this
            ->field($receive['field'])
            ->where($receive['where'])
            ->where('is_delete','=',0)
//            ->append(['role_text','identity_text'])
            ->append(['identity_text'])
            ->scope('where', $receive)
            ->paginate($receive['list_rows']);


        return $data->toArray();
    }

    //身份获取器
    public function getIdentityTextAttr($value, $data)
    {
        return config('status')['IDENTITY'][$data['identity']] ?? '';
    }


}