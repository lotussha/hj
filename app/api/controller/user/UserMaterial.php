<?php


namespace app\api\controller\user;

use app\api\controller\Api;
use app\common\model\CommonModel;
use app\common\model\material\ArticleModel;
use app\common\model\material\ArticleTypeModel;
use app\common\model\material\CoilingModel;
use app\common\model\material\NewsModel;
use app\common\model\order\OrderCommentModel;
use app\common\validate\material\ArticleValidate;
use sakuno\utils\JsonUtils;
use think\App;
use think\Request;

//素材内容
class UserMaterial extends Api
{
    public function __construct(Request $request, App $app)
    {
        parent::__construct($request, $app);
    }

    /**
     * 一键发圈
     * User: hao  2020-09-01
     */
    public function coiling(){
        $data = $this->param;
        $data['field'] = 'id,gid,img_url,video_url,title,copywriting,create_time,identity_id';
        $list = (new CoilingModel())->getAllCoiling($data);
        return JsonUtils::successful('获取成功',$list);
    }

    /**
     * 文章分类
     * User: hao  2020-09-01
     */
    public function article_type(){
        $data = $this->param;
        $data['status'] = 1;
        $list = (new ArticleTypeModel())->getTypleList($data);
        return JsonUtils::successful('获取成功',$list);
    }

    /**
     * 文章
     * User: hao  2020-09-01
     */
    public function article(){
        $data = $this->param;
        $data['field'] = 'id,title,img_url,type_id,status,content,sort,read_num,collect_num,author,create_time';
        $data['status'] = 1;

        $list = (new ArticleModel())->getAllArticle($data);
        return JsonUtils::successful('获取成功',$list);
    }

    /**
     * 文章详情
     * User: hao  2020-09-01
     */
    public function article_details(){
        $data = $this->param;
        //检验
        $validate = new ArticleValidate;
        $validate_result = $validate->scene('info')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        (new ArticleModel())->setDataInc(['id'=>$data['id']],'read_num');
        $list = (new ArticleModel())->getInfoArticle($data);
        return JsonUtils::successful('获取成功',$list);
    }

    /**
     * 消息列表
     * User: hao  2020-09-02
     */
    public function news(){
        $data = $this->param;

        $where = array();
        $where[] = ['is_show','=','1'];
        $data['where'] = $where;
        $data['field'] = 'id,title';
        $list = (new NewsModel())->getAllNews($data);
        return JsonUtils::successful('获取成功',['list'=>$list]);
    }
}