<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/7/31
 * Time: 18:02
 */

namespace app\apiadmin\model;

use think\facade\Cache;
use think\Model;

class AdminMenuModel extends Model
{
    protected $name = 'admin_menu';

    public $logMethod = [
        0 => '不记录',
        1 => 'GET',
        2 => 'POST',
        3 => 'PUT',
        4 => 'DELETE'
    ];


    public $noDeletionId = [
        1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20
    ];

    /**
     * 获取菜单
     * @return array
     * User: Jomlz
     * Date: 2020/8/1 16:58
     */
    public function getMenuList()
    {
       return self::order('sort_id', 'asc')->order('id', 'asc')->column('parent_id,name,icon', 'id');
    }

    //菜单缓存
//    public static function onAfterInsert()
//    {
//        Cache::store('user_menu')->clear();
//    }
//
//    public static function onAfterUpdate()
//    {
//        Cache::store('user_menu')->clear();
//    }
//
//    public static function onAfterDelete()
//    {
//        Cache::store('user_menu')->clear();
//    }
}