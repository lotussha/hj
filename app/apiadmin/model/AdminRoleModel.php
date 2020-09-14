<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/1
 * Time: 16:15
 */

namespace app\apiadmin\model;


class AdminRoleModel extends Model
{
    protected $name = 'admin_role';

    public $softDelete = false;

    public $noDeletionId = [
        1,2
    ];

    public static function init()
    {
    }

    protected function getUrlAttr($value)
    {
        return $value !== '' ? explode(',', $value.','. $this->defaultUrl) : [];
    }

    /**
     * 获取角色拥有的菜单
     * User: Jomlz
     * Date: 2020/8/1 17:20
     */
    public function getRoleMenuList($role_id)
    {
        $menuModel = new AdminMenuModel();
        $info = self::find($role_id);
        if (!$info){
            return ['status'=>'0','msg'=>'信息不存在'];
        }
        $menu = $menuModel->getMenuList();
        foreach ($menu as $k=>$v){
            if (in_array($v['id'],$info['url'])){
                $menu[$k]['is_checked'] = 1;
            }else{
                $menu[$k]['is_checked'] = 0;
            }
        };
        unset($info['url']);
        $tree_menu = getTree(arrString($menu));
        return ['status'=>'0','msg'=>'success','data'=>['info'=>turnString($info->toArray()),'tree_menu'=>$tree_menu]];
    }

}