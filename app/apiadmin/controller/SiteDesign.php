<?php
declare (strict_types = 1);

namespace app\apiadmin\controller;

use think\Request;
//use think\facade\View;
use app\common\validate\SiteDesignValidate as Design;
use think\exception\ValidateException;
use app\common\model\SiteDesignModel as Sdesign;
use app\JsonUtils;
use app\common\logic\HandleLogic;
class SiteDesign
{
    protected $pay_way = [
        1=>"现金",
        2=>"现金+积分",
        3=>"现金+佣金",
        4=>"现金+优惠券",
    ];

    protected $send_way = [
        1=>"快递",
        2=>"自提",
        3=>"同城配送",
    ];
    /**
     * 显示网站基本设置
     *
     * @return \think\Response
     */
    public function index()
    {
        $design = new Sdesign();
        $result = $design->getAllSiteConfig();
        if (empty($result)) { return JsonUtils::succMes($result);}
        $result = JsonUtils::changeTime($result);
        $result = $this->styleChange($result);//支付,配送方式转换
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
            validate(Design::class)->check($data);
            $design = new Sdesign();
            if(isset($data['id'])){
                $data['update_time'] = time();
                $result = $design->addSiteConfig($data);
                if ($result){
                    $res = Sdesign::getSiteConfig();
                    $res = $this->styleChange($res);
                    return JsonUtils::succMes($res);
                }else{
                    return JsonUtils::errMes("更新失败");
                }

            }else{
                $data['add_time'] = time();
                $result = $design->addSiteConfig($data);
                if ($result){
                    $res = Sdesign::getSiteConfig();
                    $res = $this->styleChange($res);
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

        $data = Sdesign::getSiteConfig($id);
        $data  = $this->styleChange($data);
//        halt($data);
        return JsonUtils::succMes($data);

    }

    /**
     * 支付方式,配送方式转换
     *made by stephen
     * @param  array $data
     * @return array $data
     */
    protected function styleChange($data){
        if (!empty($data['pay_way'])){
            $data['pay_way'] = explode(",",$data['pay_way']);
            foreach($data['pay_way'] as $key=>$val){
                if ($val ==1){
                    $data['pay_way'][$key] =$this->pay_way[1];
                }elseif ($val ==2){
                    $data['pay_way'][$key] =$this->pay_way[2];
                }elseif ($val ==3){
                    $data['pay_way'][$key] =$this->pay_way[3];
                }elseif ($val ==4){
                    $data['pay_way'][$key] =$this->pay_way[4];
                }
            }
            $data['pay_way'] = implode(",",$data['pay_way']);
        }
        if(!empty($data['send_way'])){
            $data['send_way'] = explode(",",$data['send_way']);
            foreach($data['send_way'] as $key=>$val){
                if ($val ==1){
                    $data['send_way'][$key] =$this->send_way[1];
                }elseif ($val ==2){
                    $data['send_way'][$key] =$this->send_way[2];
                }elseif ($val ==3){
                    $data['send_way'][$key] =$this->send_way[3];
                }
            }
            $data['send_way'] = implode(",",$data['send_way']);
        }
        return $data;
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
        $res = Sdesign::del($id);
//        halt($res);
        if ($res){
            return JsonUtils::succMes(["id"=>$id]);
        }else{
            return JsonUtils::errMes("删除失败");
        }
    }
}
