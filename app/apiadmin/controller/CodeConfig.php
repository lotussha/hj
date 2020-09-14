<?php
declare (strict_types = 1);

namespace app\apiadmin\controller;

use app\JsonUtils;
use think\exception\ValidateException;
use think\Request;
use app\common\validate\CodeConfigValidate as Code;
use app\common\model\CodeConfigModel as Ccode;
use app\common\logic\HandleLogic;

class CodeConfig
{
    /**
     * 显示网站基本设置
     *
     * @return \think\Response
     */
    public function index()
    {
        $design = new Ccode();
        $result = $design->getCodeConfigIndex();
        if (empty($result)) { return JsonUtils::succMes($result);}
        $result = JsonUtils::changeTime($result);
        return JsonUtils::succMes($result);
    }

    /**
     * 添加/编辑网站基本设置.
     * made by stephen
     * @return \think\Response
     */
    public function create()
    {

        $data = Request()->post();
        try {
            validate(Code::class)->check($data);
//            halt($data);
            if(isset($data["id"])){
                $data['update_time'] = time();
                $result = Ccode::createCodeConfig($data);
                if ($result){
                    $res = Ccode::getCodeConfig($data['id']);
                    $res = JsonUtils::changeTime($res);
                    return JsonUtils::succMes($res);
                }else{
                    return JsonUtils::errMes("更新失败");
                }
            }else{
                $data['add_time'] = time();
                $result = Ccode::createCodeConfig($data);
                if ($result){
                    $res = Ccode::getCodeConfig($result);
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

    /**
     * 获取编辑回显信息.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit()
    {
        $id = Request()->param("id");
        if (empty($id)){ return JsonUtils::lessParam();}

        $data = Ccode::getCodeConfig($id);
        $data = JsonUtils::changeTime($data);
        return JsonUtils::succMes($data);

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
        $res = (new Ccode())->del($id);
        if ($res){
            return JsonUtils::succMes(["id"=>$id]);
        }else{
            return JsonUtils::errMes("删除失败");
        }
    }
}
