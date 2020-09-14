<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/8/11 11:14
 *                ..::::::::::::.                                女神保佑，代码无bug！！！
 *              ``::::::::::::::::                               Codes are far away from bugs with the goddess！！！
 *               ::::``:::::::::'        .:::.
 *              ::::'   ':::::'       .::::::::.
 *            .::::'      ::::     .:::::::'::::.
 *           .:::'       :::::  .:::::::::' ':::::.
 *          .::'        :::::.:::::::::'      ':::::.
 *         .::'         ::::::::::::::'         ``::::.
 *     ...:::           ::::::::::::'              ``::.
 *    ````':.          ':::::::::'                  ::::..
 *                       '.:::::'                    ':'````..
 *
 */
namespace app\common\model\cookbook;


use app\common\model\CommonModel;

/**
 * 营销板块 - 菜谱model
 * Class MarkeringMenuModel
 * @package app\common\model\cookbook
 */
class MarkeringMenuModel extends CommonModel
{
    protected $name = 'markering_menu';

    /**
     * 一对一关联分类模型
     * @return \think\model\relation\HasOne
     */
    public function MenuCategory() {
        return $this->hasOne('MarkeringMenuCategoryModel', 'id', 'cate_id')->field('id,category_title');
    }

    /**
     * 获取菜谱列表(分页)
     * @param array|null $where
     * @param int $limit
     * @param string $field
     * @param array|null $order
     * @param array|null $hidden
     * @return array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMarkeringMenuListsPage(?array $where = [], int $limit = 10, string $field = '*', ?array $order = [],?array $hidden = []){
        $res = $this->where($where)
            ->field($field)
            ->hidden($hidden)
            ->limit($limit)
            ->order($order)
            ->with(['menu_category' => function($query){
                $query->hidden(['id']);
            }])->select();
        if(!empty($res)){
            $res = $res->toArray();
        }
        return $res;
    }

}
