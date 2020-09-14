<?php
declare (strict_types = 1);

namespace app\apiadmin\controller;
use app\common\validate\SendCompanyValidate as Send;
use think\exception\ValidateException;
use app\common\model\SendCompanyModel as Ssend;
use think\Request;
use app\JsonUtils;
use app\common\logic\HandleLogic;

class SendCompany
{

    /**
     * 显示快递公司列表
     *made by stephen
     * @return \think\Response
     */
    public function index()
    {
        $condition = Request()->param();
        $data = Ssend::getCodeIndex($condition);
        $data = Jso::changeTime($data);
        return JsonUtils::succMes($data);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $data = Request()->post();
        try {
            validate(Send::class)->check($data);
            if(isset($data['id'])){
                $data['update_time']=time();
                $result = Ssend::createSendCompany($data);
                if ($result){
                    $res = Ssend::getMess($data['id']);
                    $res = JsonUtils::changeTime($res);
                    return JsonUtils::succMes($res);
                }else{
                    return JsonUtils::errMes("更新失败");
                }
            }else{
                $data['add_time'] = time();
                $result = Ssend::createSendCompany($data);
                if ($result){
                    $res = Ssend::getMess($result);
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
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        if(!$id){
            return JsonUtils::lessParam();
        }
        $data = Ssend::getMess($id);
        return JsonUtils::succMes($data);

    }

    /**
     * 开启,关闭快递公司
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function isOpen()
    {
        $data = Request()->param();
        if (empty($data['id'])){
            return JsonUtils::lessParam();
        }
        if (empty($data['isOpen'])){
            return JsonUtils::lessParam();
        }
        $res = Ssend::isOpen($data['id'],$data['isOpen']);
        if ($res){
            return JsonUtils::succMes(['id'=>$data['id']]);
        }else{
            return JsonUtils::errMes("开启失败");
        }
    }

    /**
     * 开启,关闭快递公司
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (empty($id)){
            return JsonUtils::lessParam();
        }
        $res = Ssend::del($id);
        if ($res){
            return JsonUtils::succMes(['id'=>$id]);
        }else{
            return JsonUtils::errMes("删除失败");
        }
    }

}
