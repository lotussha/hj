<?php


namespace app\api\controller\user;


use app\api\controller\Api;
use app\common\logic\settlement\SettlementLogic;
use app\common\logic\user\UserDetailsLogic;
use app\common\logic\user\UserGoodsLogic;
use app\common\model\order\OrderCommentModel;
use app\common\validate\settlement\SettlementValidate;
use app\common\validate\user\UserApiValidate;
use sakuno\utils\JsonUtils;
use think\App;
use think\Request;
use think\response\Json;

//用户操作商品
class UserGoods extends Api
{

    protected $userGoodsLogic;
    protected $validate;

    public function __construct(Request $request, App $app)
    {
        $this->userGoodsLogic = new UserGoodsLogic();
        $this->validate = new UserApiValidate();
        parent::__construct($request, $app);
    }

    /**
     * 用户评论
     * User: hao  2020-8-29
     */
    public function comment()
    {
        $data = $this->param;
        $data['uid'] = $this->api_user['id'];
//        $data['uid'] = 1;
        //检验
        $validate_resule = $this->validate->scene('comment')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($this->validate->getError(), PARAM_IS_INVALID);
        }

        $res = $this->userGoodsLogic->comment($data);
        return $res;
    }

    /**
     * 用户评论列表
     * User: hao  2020-8-29
     */
    public function comment_list()
    {
        $data = $this->param;
        $data['examine_is'] = 1;
        //检验
        $validate_resule = $this->validate->scene('comment_list')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($this->validate->getError(), PARAM_IS_INVALID);
        }
        $data['field'] = 'id,gid,order_id,rec_id,content,img_url,create_time,uid,goods_name,spec_key_name';
        $list = (new OrderCommentModel())->getAllComment($data);
        return JsonUtils::successful('操作成功', $list);
    }

    /**
     * 收藏
     * User: hao  2020-8-29
     */
    public function collect()
    {
        $data = $this->param;
        //检验
        $validate_resule = $this->validate->scene('collect')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($this->validate->getError(), PARAM_IS_INVALID);
        }
        $data['uid'] = $this->api_user['id'];
        $res = $this->userGoodsLogic->collect($data);
        return $res;
    }

    /**
     * 收藏商品列表（好物圈）
     * User: hao  2020-8-29
     */
    public function goods_collect()
    {
        $data = $this->param;
        //检验
        $validate_resule = $this->validate->scene('goods_collect')->check($data);
        if (!$validate_resule) {
            return JsonUtils::fail($this->validate->getError(), PARAM_IS_INVALID);
        }
        $data['uid'] = $this->api_user['id'];
        $res = $this->userGoodsLogic->goods_collect($data);
        return $res;
    }


    /**
     * 收藏店铺列表
     * User: hao  2020-8-29
     */
    public function shop_collect()
    {
        $data = $this->param;
        $data['uid'] = $this->api_user['id'];
        $res = $this->userGoodsLogic->shop_collect($data);
        return $res;
    }

    /**
     * 收藏菜谱
     * User: hao  2020-8-29
     */
    public function markering_collect(){
        $data = $this->param;
        $data['uid'] = $this->api_user['id'];
        $res = $this->userGoodsLogic->markering_collect($data);
        return $res;
    }
    /**
     * 我的评论
     * User: hao  2020-09-03
     */
    public function me_comment(){
        $data = $this->param;
        $data['uid'] = $this->api_user['id'];

        $comment = new OrderCommentModel();
        $where = array();
//        $where[] = ['status','=','1'];
//        $where[] = ['examine_is','=','1'];

        $data['where'] = $where;
        $data['field'] = 'id,create_time,content,img_url,spec_key_name,gid,uid';
        $list = $comment->getAllComment($data);

        return JsonUtils::successful('操作成功', $list);


    }

}