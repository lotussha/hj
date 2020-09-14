<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/3
 * Time: 11:02
 */

namespace app\common\model;

use app\apiadmin\model\Model;
use think\facade\Db;

class GoodsCategoryModel extends Model
{
    protected $name = 'goods_category';

    /**
     * 获取分类树结构
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * User: Jomlz
     * Date: 2020/8/3 18:47
     */
    public function getCatTreeList($level = 4)
    {
//        $lists = $this->where([['level','<',$level],['is_show','=',1]])->cache(true)->order('sort asc,id desc')->select()->toArray(); //限制最多四级
        $lists = $this->where([['level','<',$level],['is_del','=',0]])->order('sort asc,id desc')->select()->toArray(); //限制最多四级
        return getTree(arrString($lists));
    }

    /**
     * 递归重整数组
     * @param $array
     * @param int $pid
     * @param int $level
     * @return array
     * User: Jomlz
     * Date: 2020/8/3 11:17
     */
    public function recursion($array, $pid =0, $level = 1){
        //声明静态数组,避免递归调用时,多次声明导致数组覆盖
        static $list = [];
        foreach ($array as $key => $value){
            //第一次遍历,找到父节点为根节点的节点 也就是pid=0的节点
            if ($value['parent_id'] == $pid){
                //父节点为根节点的节点,级别为0，也就是第一级
                $value['level'] = $level;
                //把数组放到list中
                $list[] = $value;
                //把这个节点从数组中移除,减少后续递归消耗
                unset($array[$key]);
                //开始递归,查找父ID为该节点ID的节点,级别则为原级别+1
                $this->recursion($array, $value['id'], $level+1);
            }
        }
        return $list;
    }

    public function getCatList($field = '*',$where = [],$order = '',$page = 1 ,$limit = 10)
    {
        return $this->field($field)->where($where)->page($page,$limit)->order($order)->select()->toArray();
    }
}