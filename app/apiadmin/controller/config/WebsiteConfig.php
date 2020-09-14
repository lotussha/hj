<?php


namespace app\apiadmin\controller\config;

//网站设置
use app\apiadmin\controller\Base;
use app\common\logic\config\WebsiteConfigLogic;
use app\common\model\config\WebsiteConfigModel;
use app\common\validate\config\WebsiteConfigValidate;
use app\Request;
use sakuno\services\UtilService;
use sakuno\utils\JsonUtils;
//网站设置
class WebsiteConfig extends Base
{
    /**
     * 网站设置详情
     * @return array
     * @author hao    2020.08.17
     * */
    public function index(Request $request){
        //获取数据
        list($config_type) = UtilService::postMore([
            ['config_type', ''],
        ], $request, true);
        $WebsiteConfigModel = new WebsiteConfigModel();
        //检验
        $validate = new WebsiteConfigValidate();
        $validate_resule = $validate->scene('index')->check(['config_type' => $config_type]);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), 10001);
        }
        //处理
        $Logic = new WebsiteConfigLogic();

        $res = $Logic->info($config_type);
        return $res;


    }

    /**
     * 修改网站设置
     * @return array
     * @author hao    2020.08.18
     * */
    public function edit(Request $request){
        //获取数据
        list($config_type,$val) = UtilService::postMore([
            ['config_type', ''],
            ['val', ''],
        ], $request, true);

        //检验
        $validate = new WebsiteConfigValidate();
        $validate_resule = $validate->scene('edit')->check(['config_type' => $config_type,'val'=>$val]);
        if (!$validate_resule) {
            return JsonUtils::fail($validate->getError(), PARAM_IS_INVALID);
        }

        $data = array();
        $data['config_type'] = $config_type;
        $data['val'] = $val;
        //处理
        $Logic = new WebsiteConfigLogic();
        $res = $Logic->edit($data);
        return $res;
    }
}