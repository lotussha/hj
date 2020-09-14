<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/26
 * Time: 14:29
 */

namespace app\api\controller\goods;

use app\api\controller\Api;
use app\common\model\GoodsCategoryModel;
use app\Request;
use sakuno\utils\JsonUtils;
use think\App;

class GoodsCategory extends Api
{
    protected $goodsCategoryModel;
    public function __construct(Request $request, App $app , GoodsCategoryModel $goodsCategoryModel)
    {
        $this->goodsCategoryModel = $goodsCategoryModel;
        parent::__construct($request, $app);
    }

    //商品分类
    public function lists()
    {
        $goods_category_tree = $this->goodsCategoryModel->getCatTreeList();
        $data['cat_tree'] = $goods_category_tree;
        return JsonUtils::successful('获取成功',$data);
    }

    //推荐分类
    public function recommend()
    {
        $where = [];
        if (isset($this->param['parent_id'])){
            $where[] = ['parent_id','=',$this->param['parent_id']];
        }
        $cat_list = $this->goodsCategoryModel
            ->field(['id,name,image,parent_id'])
            ->where(['level'=>3,'is_hot'=>1,'is_show'=>1,'is_del'=>0])
            ->where($where)
            ->order('sort asc ,id desc')
            ->cache(true)
            ->select();
        $data['cat_list'] = $cat_list;
        return JsonUtils::successful('获取成功',$data);
    }
}