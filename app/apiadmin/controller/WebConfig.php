<?php
declare (strict_types = 1);

namespace app\apiadmin\controller;

use app\JsonUtils;
use think\exception\ValidateException;
use think\Request;
use \app\common\model\WebConfigModel as wDesign;
use \app\common\validate\WebConfigValidate as wD;
use app\common\logic\HandleLogic;
class webConfig
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $design = new wDesign();
        $result = $design->getSiteConfig();
        if (empty($result)) { return JsonUtils::succMes($result);}
        $result = JsonUtils::changeTime($result);
        return JsonUtils::succMes($result);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $data = Request()->post();
//        halt(new  wDesign());
        try {
            validate(wD::class)->check($data);
            if(isset($data['id'])){
                $data['update_time'] = time();
                $result = (new wDesign())->addWebSiteConfig($data);
                if ($result){
                    $res = (new wDesign())->getNowSiteConfig($data['id']);
                    $res = JsonUtils::changeTime($res);
                    return JsonUtils::succMes($res);
                }else{
                    return JsonUtils::errMes("更新失败");
                }

            }else{
                $data['add_time'] = time();
                $result = (new wDesign())->addWebSiteConfig($data);
                if ($result){
                    $res = (new wDesign())->getNowSiteConfig($result);
                    $res = JsonUtils::changeTime($res);
                    return JsonUtils::succMes($res);
                }else{
                    return JsonUtils::errMes("添加失败");
                }
            }

        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            return JsonUtils::errMes($e->getError());
        }

    }


    public function edit($id)
    {
        if (empty($id)){
            return JsonUtils::lessParam();
        }
        $design = new wDesign();
        $result = $design->getNowSiteConfig($id);
        if (empty($result)) { return JsonUtils::succMes($result);}
        $result = JsonUtils::changeTime($result);
        return JsonUtils::succMes($result);
    }



    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (empty($id)){
            return JsonUtils::lessParam();
        }
        $res = wDesign::del($id);
//        halt($res);
        if ($res){
            return JsonUtils::succMes(["id"=>$id]);
        }else{
            return JsonUtils::errMes("删除失败");
        }
    }
}
