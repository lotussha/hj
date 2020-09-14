<?php
declare (strict_types = 1);

namespace app\apiadmin\controller;

use app\JsonUtils;
use think\Db;
use think\exception\ValidateException;
use think\Request;
use \app\apiadmin\model\withdraw as WD;
use \app\apiadmin\validate\withdraw as wDraw;
use app\common\logic\HandleLogic;

class withdraw
{
    /**
     * 显示快递公司列表
     *made by stephen
     * @return \think\Response
     */
    public function index()
    {
        $data = WD::getWithdrawIndex();
        $data = JsonUtils::changeTime($data);
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
            validate(wDraw::class)->check($data);//验证数据
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            return JsonUtils::errMes($e->getError());
        }
        if(isset($data['id'])&& $data['id']!= ''){//根据id判断更新还是添加
            $data['update_time'] = time();
            $result = WD::createWithdraw($data);//执行更新操作
            if ($result){
                $res = WD::getMess($data['id']);//获取返显信息
                $res = JsonUtils::changeTime($res);//转换时间戳
                return JsonUtils::succMes($res);//返回信息
            }else{
                return JsonUtils::errMes("更新失败");//更新失败返回信息
            }
        }else{
            unset($data['id']);
            $data['add_time'] = time();
            $result = WD::createWithdraw($data);
            if ($result){
                $res = WD::getMess($result);
                $res = JsonUtils::changeTime($res);
                return JsonUtils::succMes($res);
            }else{
                return JsonUtils::errMes("添加失败");
            }
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
        $data = WD::getMess($id);
        $data = JsonUtils::changeTime($data);
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
//        halt($data);
        if (empty($data['id'])){
            return JsonUtils::lessParam();
        }
        if (empty($data['is_withdraw'])){
            return JsonUtils::lessParam();
        }
        $res = WD::isOpen($data['id'],$data['is_withdraw']);
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
        $res = WD::del($id);
        if ($res){
            return JsonUtils::succMes(['id'=>$id]);
        }else{
            return JsonUtils::errMes("删除失败");
        }
    }
}
