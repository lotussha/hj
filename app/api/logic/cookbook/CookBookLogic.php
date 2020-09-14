<?php
/**
 * Created by PhpStorm.
 * User: -li
 * Date: 2020/9/2
 * Time: 12:02
 */

namespace app\api\logic\cookbook;

use app\api\logic\activity\SeckillLogic;
use app\apiadmin\model\AdminUsers;
use app\common\logic\goods\GoodsPromFactory;
use app\common\model\ActivityModel;
use app\common\model\FreightTemplateModel;
use app\common\model\CookBookModel;
use app\common\model\settlement\SettlementModel;
use sakuno\utils\JsonUtils;
use think\facade\Db;
use app\common\model\user\UserModel;
use app\common\model\GoodsModel;
use app\common\model\cookbook\MarkeringMenuCategoryModel;
use app\common\model\cookbook\CookBookCommentModel;

class CookBookLogic
{
    protected $cookbookModel;
    protected $settlementModel;
    protected $seckilllogic;
    public function __construct(CookBookModel $cookbookModel,SettlementModel $settlementModel,SeckillLogic $seckillLogic)
    {
        $this->cookbookModel = $cookbookModel;
        $this->settlementModel = $settlementModel;
        $this->seckilllogic = $seckillLogic;
    }

    /**
     * 获取菜谱列表
     * User: -li
     */
    public function getCookBookList($param=[])
    {
        $page = isset($param['page']) && !empty($param['page']) ? $param['page'] : 1;
        $limitpage = isset($param['limitpage']) && !empty($param['limitpage']) ? $param['limitpage'] : 10;
        $where = $param['where'] ?? '';
        $field = $param['field'] ?? '';
        $lists = $this->cookbookModel
            ->field($field)
            ->where(['is_delete'=>0,'status'=>1])
            ->where($where)
            ->scope('where', $param)
            //->cache(true)
            ->page($page, $limitpage)
            ->select()->toArray();
            //echo  $this->cookbookModel->getLastSql();die;
        foreach ($lists as $key => $value) {
            $User  = (new UserModel())->findInfo(['id'=>$value['user_id']],'nick_name,avatar_url');
            $lists[$key]['user_name'] = $User['nick_name']; 
            $lists[$key]['user_img']  = $User['avatar_url']; 
        }    
       return $lists;
    }

    /**
     * 获取菜谱详情
     * User: -li
     */
    public function getCookBookDetails($param=[])
    {
        $field = 'id,menu_title,menu_synopsis,food_ingredients,main_images,menu_videos,menu_details,difficulty,cooking_time,user_id,main_material,auxiliary_material,main_goods_id,auxiliary_goods_id,collection';
        $data = $this->cookbookModel
            ->field($field)
            // ->append(['service_arr'])
            ->where(['id'=>$param['id'],'is_delete'=>0,'status'=>1])
            ->find()->toArray();
         //获取关联会员信息
        $User  = (new UserModel())->findInfo(['id'=>$data['user_id']],'nick_name,avatar_url');
        $data['user_name'] = $User['nick_name']; 
        $data['user_img']  = $User['avatar_url'];
        //获取主料商品图片
        $data['goods_img1'] = (new GoodsModel())->where('goods_id', 'in', $data['main_goods_id'])->column('original_img');
        //获取辅料商品图片
        $data['goods_img2'] = (new GoodsModel())->where('goods_id', 'in', $data['auxiliary_goods_id'])->column('original_img');   
            
        if (empty($data)){
            return ['status'=>0,'msg'=>'菜谱食材不存在'];
        }
       
        return ['status'=>1,'msg'=>'获取菜谱成功','data'=>$data];
    }

    /**
     * 获取菜谱一级分类
     * User: -li
     */
    public function getCookBookCategory(){

        $where = ['is_delete'=>0,'status'=>1];
        $result = (new MarkeringMenuCategoryModel())->getList($where,'id,category_title,sort',['sort'=>'asc','id'=>'desc']);
        return $result;
    }


    /**
     * 菜谱详情--添加用户评论
     * User: -li  
     */
    public function getCookBookcomment($receive)
    {
        
        if (isset($receive['img_url'])) {
            // if (!isImg($receive['img_url'])) {
            //     return JsonUtils::fail('图片格式有误');
            // }
            $img = explode(',', $receive['img_url']);
            if (count($img) > 6) {
                return JsonUtils::fail('图片不能超过6张');
            }
        }
        $data_comment = array();
        $data_comment['gid'] = $receive['gid'];        //关联的菜谱ID
        $data_comment['uid'] = $receive['uid'];        //评论者的ID
        $data_comment['create_time'] = time();         //评论时间
        $data_comment['content'] = $receive['content'];//评论内容
        $data_comment['img_url'] = $receive['img_url'];//上传图片地址
        $data_comment['ip'] = $_SERVER['REMOTE_ADDR']; //评论者的ip地址

        Db::startTrans();
        try {
            $res = (new CookBookCommentModel())->addInfo($data_comment);
            if (!$res) {
                Db::rollback();
                return JsonUtils::fail('评论失败');
            }
            Db::commit();
            return JsonUtils::successful('评论成功');
        } catch (\Exception $e) {
            Db::rollback();
            return JsonUtils::fail('评论失败1');
        }
    }

    /**
     * 菜谱详情--追加评论回复
     * User: -li  
     */
    public function ReplyCookBookComment($receive)
    {
        
        $data = array();
        $data['gid'] = $receive['gid'];        //关联的菜谱ID
        $data['uid'] = $receive['uid'];        //回复者的ID
        $data['content'] = $receive['content'];//回复内容
        $data['ip'] = $_SERVER['REMOTE_ADDR']; //回复用户的ip
        $data['create_time'] = time();//回复时间
        $data['chase_comment_id'] = $receive['comment_id'];//追评的记录ID

        Db::startTrans();
        try {
            $res = (new CookBookCommentModel())->addInfo($data);
            if (!$res) {
                Db::rollback();
                return JsonUtils::fail('回复评论失败');
            }
            Db::commit();
            return JsonUtils::successful('回复成功');
        } catch (\Exception $e) {
            Db::rollback();
            return JsonUtils::fail('回复评论失败');
        }
    }

    /**
     * 菜谱详情--用户评论列表
     * User: -li  
     */
    public function getAllCookBookComment($receive)
    {
        
        $receive['list_rows'] = isset($receive['list_rows'])?$receive['list_rows']:10;  //多少条
        $receive['field'] = isset($receive['field'])?$receive['field']:'';//指定字段

        $data = (new CookBookCommentModel())
            ->where('is_delete','<>','1')
            ->where('groups','=','0')
            ->field($receive['field'])
            ->scope('where', $receive)
            ->paginate($receive['list_rows']);
            
        foreach ($data as $k => $v) {
            $User  = (new UserModel())->findInfo(['id'=>$v['uid']],'nick_name,avatar_url');
            $data[$k]['user_name'] = $User['nick_name']; 
            $data[$k]['user_img']  = $User['avatar_url']; 
        } 
        return $data->toArray();

    }


    /**
     * 菜谱详情--用户评论详情
     * User: -li  
     */
    public function getAllDetails($receive)
    {
        
        $receive['list_rows'] = isset($receive['list_rows'])?$receive['list_rows']:10;  //多少条
        $receive['field'] = isset($receive['field'])?$receive['field']:'';//指定字段

        $data = (new CookBookCommentModel())
            ->where('is_delete','<>','1')
            ->where('groups','=','0')
            ->field($receive['field'])
            ->scope('where', $receive)
            ->paginate($receive['list_rows']);
        

        $data = $this->get_childs_comment($data,0,0);
             
        // foreach ($data as $k => $v) {
        //     $User  = (new UserModel())->findInfo(['id'=>$v['uid']],'nick_name,avatar_url');
        //     $data[$k]['user_name'] = $User['nick_name']; 
        //     $data[$k]['user_img']  = $User['avatar_url']; 
        // } 
        return $data;

    }

    /**
     * 递归评论回复内容
     * User: -li  
     */
    public function get_childs_comment($comments, $parent_id = 0, $level = 0)
    {
        $new_comments = [];

        foreach ($comments as $key => $val) {
            
            $User  = (new UserModel())->findInfo(['id'=>$val['uid']],'nick_name,avatar_url');
            $val['user_name'] = $User['nick_name']; 
            $val['user_img']  = $User['avatar_url'];

            if ($val['chase_comment_id'] == $parent_id) {

                $val['level'] = $level;

                $val['childs'] = $this->get_childs_comment($comments, $val['id'], $level + 1);

                $new_comments[] = $val;

            }

        }
        return $new_comments;

    }


}