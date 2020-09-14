<?php
declare (strict_types = 1);

namespace app\apiadmin\controller;

use app\common\model\RegionConfigModel;
use app\JsonUtils;
use think\Request;

class RegionConfig
{
    /**
 * 显示可送货区域列表
 *
 * @return \think\Response
 */
    public function index()
    {
        $data = Request()->param();
        $result = RegionConfigModel::getRegionIndex($data);
        return JsonUtils::succMes($result);
    }


    /**
     * 显示下级可送货区域列表
     *
     * @return \think\Response
     */
    public function LowerCity()
    {
        $data = Request()->param();
        JsonUtils::lessParam();
        if (empty($data['level'])){
            JsonUtils::lessParam();
        }
        $result = RegionConfigModel::getLowerRegionIndex($data);
        return JsonUtils::succMes($result);
    }
    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $data = Request()->param();
        if (empty($data['region_name'])){
            return JsonUtils::lessParam();
        }
        $res = RegionConfigModel::createSendArea($data);
        if ($res){
            return JsonUtils::succMes(["message"=>"添加成功"]);
        }else{
            return JsonUtils::errMes("添加失败,请勿重复添加");
        }
    }


    /**
     * 显示可配送区域链
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id,$type=1)
    {
//        $data  = Request()->param();
        if (empty($id)){
            return JsonUtils::lessParam();
        }
        $res = RegionConfigModel::getRegionName($id);
        if($res){
            $num = count($res);
//            halt($num);
            $area_list = '';
            if($num == 1){
                $area_list = '';
                return JsonUtils::succMes(["area_list"=>'']);
            }else{
                for($i=$num-1;$i>=1;$i--){
                    $area_list .= '>'.$res[$i];
                }
                $area_list = mb_substr($area_list,1);
            }
        }
        if($type = 2){//内部调用
            return $area_list;
        }
//        halt($area_list);
        return JsonUtils::succMes(["area_list"=>$area_list]);

    }

    /**
     * 返回上级可配送区域菜单.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function getUpperList()
    {
        $id  = Request()->param('parent_id');
        $level  = Request()->param('level');
        if ($id === ''){
            return JsonUtils::lessParam();
        }
        if ($level ===''){
            return JsonUtils::lessParam();
        }
        if ($id == 0 ){
            return JsonUtils::succMes(["message"=>"已是最顶级,请勿重复请求"]);
        }
        $res = RegionConfigModel::getUpperList($level);
        $list = $this->read($id,2);
//        halt($list);
        $data = ['area_list'=>$list,'area_message'=>$res];
        return JsonUtils::succMes($data);

    }


    /**
     * 删除可配送区域
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (empty($id)){
            return JsonUtils::lessParam();
        }
        $res  = RegionConfigModel::del($id);
        if ($res){
            return JsonUtils::succMes(['id'=>$id]);
        }else{
            return JsonUtils::errMes("删除失败");
        }
    }
}
