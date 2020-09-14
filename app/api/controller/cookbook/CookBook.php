<?php
/**
 * Created by PhpStorm.
 * User: -li
 * Date: 2020/8/31
 * Time: 19:47
 */

namespace app\api\controller\cookbook;

use app\api\controller\Api;
use app\api\logic\cookbook\CookBookLogic;
use app\Request;
use sakuno\utils\JsonUtils;
use think\App;
use think\facade\Db;
use app\common\validate\cookbook\CookBookValidate;

class CookBook extends Api
{
    protected $cookbooklogic;
    protected $seckilllogic;
    public function __construct(Request $request, App $app ,CookBookLogic $cookbooklogic,CookBookValidate $cookbookvalidate)
    {
        $this->cookbooklogic = $cookbooklogic;
        $this->validate      = $cookbookvalidate;
        parent::__construct($request, $app);
    }

    /**
     * 获取菜谱列表
     * @return \think\Response
     * User: -li
     */
    public function get_lists()
    {

        // $cookbooklogic = new \app\common\logic\cookbook\CookBookLogic();
        $this->param['field'] = 'id,cate_id,main_images,menu_title,difficulty,cooking_time,collection,is_recommend,user_id';
        // 筛选 一级分类 是否推荐 菜谱名称
        $this->param['where']['cate_id'] = $this->param['cate_id'] ?? 1; //加入筛选条件中
        $this->param['where']['is_recommend'] = $this->param['is_recommend'] ?? ''; //加入筛选条件中
        $this->param['where']['menu_title'] = $this->param['menu_title'] ?? ''; //加入筛选条件中
    
        //查询条件
        $lists = $this->cookbooklogic->getCookBookList($this->param);
        return JsonUtils::successful('获取成功',$lists);
    }

    /**
     * 获取菜谱详情信息
     * User: -li
     * Date: 2020/9/1 14:56
     */
    public function get_details()
    {

        $cookbooklogic = new \app\common\logic\cookbook\CookBookLogic();
        $res = $this->cookbooklogic->getCookBookDetails($this->param);
        if ($res['status'] == 0){
            return JsonUtils::fail($res['msg']);
        }
        return JsonUtils::successful('获取详情成功',$res['data']);
    }

    /**
     * 获取菜谱分类
     * User: -li
     * Date: 2020/9/1 14:56
     */
    public function get_category()
    {
        $cookbooklogic = new \app\common\logic\cookbook\CookBookLogic();
        $data = $this->cookbooklogic->getCookBookCategory();
        return JsonUtils::successful('获取分类成功',$data);

    }

    /**
     * 菜谱用户增加评论
     * User: -li
     * Date: 2020/9/1 20:56
     */
    public function ck_comment()
    {
        $data = $this->param;
        $data['uid'] = $this->api_user['id'];
        //检验
        $validate_resule = $this->validate->scene('ck_comment')->check($data);//添加评论
        if (!$validate_resule) {
            return JsonUtils::fail($this->validate->getError(), PARAM_IS_INVALID);
        }

        $res = $this->cookbooklogic->getCookBookcomment($data);
        return $res;

    }

    /**
     * 菜谱--追加评论回复
     * User: -li
     * Date: 2020/9/2
     */
    public function reply_comment()
    {
        $data = $this->param;
        $data['uid'] = $this->api_user['id'];
        //检验
        $validate_resule = $this->validate->scene('reply_comment')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($this->validate->getError(), PARAM_IS_INVALID);
        }

        $res = $this->cookbooklogic->ReplyCookBookComment($data);//回复
        return $res;

    }

    /**
     * 菜谱用户评论列表
     * User: -li
     * Date: 2020/9/2 
     */
    public function ck_comment_list()
    {

        $data = $this->param;
        //检验
        $validate_resule = $this->validate->scene('comment_list')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($this->validate->getError(), PARAM_IS_INVALID);
        }
        $data['field'] = 'id,gid,content,img_url,create_time,uid';
        $list = $this->cookbooklogic->getAllCookBookComment($data);//评论列表
        return JsonUtils::successful('操作成功', $list);

    }

    /**
     * 菜谱--评论详情列表
     * User: -li
     * Date: 2020/9/2 
     */
    public function comment_details()
    {

        $data = $this->param;
        //检验
        $validate_resule = $this->validate->scene('comment_list')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($this->validate->getError(), PARAM_IS_INVALID);
        }
        $data['field'] = 'id,gid,content,img_url,create_time,uid,chase_comment_id';
        $list = $this->cookbooklogic->getAllDetails($data);//评论列表
        return JsonUtils::successful('操作成功', $list);

    }




}