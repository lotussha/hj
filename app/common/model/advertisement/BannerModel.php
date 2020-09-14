<?php


namespace app\common\model\advertisement;


use app\common\model\CommonModel;
use app\common\model\cookbook\MarkeringMenuModel;
use app\common\model\material\ArticleModel;
use think\Model;
use think\model\concern\SoftDelete;
use app\apiadmin\model\AdminUsers;

class BannerModel extends CommonModel
{
//    use SoftDelete; // 一开启这个 软删除后，就没有数据了
//    protected $deleteTime = 'delete_time';
    protected $name = 'banner';

    //可作为条件的字段
    protected $whereField = [
        'position_id',
        'identity_id',
    ];

    //可搜索字段
    protected $searchField = [
        'name'
    ];

    /**
     * 轮播图位置
     * User: hao
     * Date: 2020-08-10
     * @return \think\model\relation\hasOne
     */
    public function bannerPosition()
    {
        return $this->hasOne('BannerPositionModel', 'id', 'position_id')->field('id,name');
    }

    /**
     *购买商品
     * User: hao
     * Date: 2020-09-05
     * @return \think\model\relation\hasOne
     */
    public function goods()
    {
        return $this->hasOne('app\common\model\GoodsModel', 'goods_id', 'link_id')->field('goods_id,goods_name,original_img');
    }

    /**
     *管理员（店铺)
     * User: hao
     * Date: 2020-09-05
     * @return \think\model\relation\hasOne
     */
    public function admin()
    {
        return $this->hasOne(AdminUsers::class, 'id', 'link_id')->field('id,username,avatar');
    }

    /**
     *文章
     * User: hao
     * Date: 2020-09-05
     * @return \think\model\relation\hasOne
     */
    public function article()
    {
        return $this->hasOne(ArticleModel::class, 'id', 'link_id')->field('id,title,img_url');
    }

    /**
     *菜谱
     * User: hao
     * Date: 2020-09-05
     * @return \think\model\relation\hasOne
     */
    public function markeringMenu()
    {
        return $this->hasOne(MarkeringMenuModel::class, 'id', 'link_id')->field('id,menu_title,main_images');
    }


    /**
     * 获取列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * User: hao
     * Date: 2020/8/10
     */
    public function getAllBanner($receive)
    {
        $receive['list_rows'] = isset($receive['list_rows']) ? $receive['list_rows'] : 10;  //多少条
        $receive['field'] = isset($receive['field']) ? $receive['field'] : '';//指定字段

        $data = $this->with(['bannerPosition', 'goods', 'admin', 'article', 'markeringMenu'])
            ->field($receive['field'])
            ->where('is_delete','=',0)
            ->scope('where', $receive)
            ->order('')
            ->paginate($receive['list_rows']);
        foreach ($data as $k => $v) {
            $v['position_name'] = $v->bannerPosition['name'];
            $v['banner_name'] = '';
            $v['banner_img'] = '';
            switch ($v['skip_type']) {
                //商品
                case 1:
                    $v['banner_name'] = $v->goods['goods_name'];
                    $v['banner_img'] = $v->goods['original_img'];
                    break;
                case 2:
                    $v['banner_name'] = $v->admin['username'];
                    $v['banner_img'] = $v->admin['avatar'];
                    break;
                case 3:
                    $v['banner_name'] = $v->article['title'];
                    $v['banner_img'] = $v->article['img_url'];
                    break;
                case 4:
                    $v['banner_name'] = $v->markeringMenu['menu_title'];
                    $v['banner_img'] = $v->markeringMenu['main_images'];
                    break;
            }
            unset($v->goods);
            unset($v->admin);
            unset($v->article);
            unset($v->markeringMenu);
            unset($v->bannerPosition);
            $data[$k] = $v;
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
     * Date: 2020/8/10
     */
    public function getInfoBanner($where, $field = '')
    {
        $field = $field ? $field : 'id,name,img_url,type,status,sort,link_id,img_url,position_id,start_time,end_time,background,skip_type';
        $data = $this->with(['bannerPosition' => function ($query) {
            $query->field('name');
        }])
            ->field($field)
            ->where($where)
            ->find();

        $data['position_name'] = $data->bannerPosition['name'];
        $data['banner_name'] = '';
        $data['banner_img'] = '';
        switch ($data['skip_type']) {
            //商品
            case 1:
                $data['banner_name'] = $data->goods['goods_name'];
                $data['banner_img'] = $data->goods['original_img'];
                break;
            case 2:
                $data['banner_name'] = $data->admin['username'];
                $data['banner_img'] = $data->admin['avatar'];
                break;
            case 3:
                $data['banner_name'] = $data->article['title'];
                $data['banner_img'] = $data->article['img_url'];
                break;
            case 4:
                $data['banner_name'] = $data->markeringMenu['menu_title'];
                $data['banner_img'] = $data->markeringMenu['main_images'];
                break;
        }
        unset($data->goods);
        unset($data->admin);
        unset($data->article);
        unset($data->markeringMenu);
        unset($data->bannerPosition);
        if ($data) {
            $data = $data->toArray();
        } else {
            $data = array();
        }
        return $data;
    }


    //开始时间
    public function getStartTimeAttr($v)
    {
        return date('Y-m-d H:i:s', $v);
    }

    //结束时间
    public function getEndTimeAttr($v)
    {
        return date('Y-m-d H:i:s', $v);
    }


}