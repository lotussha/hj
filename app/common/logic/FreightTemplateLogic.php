<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/11
 * Time: 15:49
 */

namespace app\common\logic;

use app\common\model\FreightTemplateModel;
use app\common\model\GoodsModel;
use app\common\validate\FreightTemplateValidate;
use sakuno\utils\JsonUtils;
use think\facade\Db;

class FreightTemplateLogic
{
    /**
     * 获取运费模板
     * User: Jomlz
     * Date: 2020/8/11 17:43
     */
    public function freightTemplateLists($param=[])
    {
        $model = new FreightTemplateModel();
        $where = ['is_del'=>0];
        $field = '';
        $page = isset($param['page']) ?? 1;
        $lists = $model->field($field)->append(['type_desc'])->where($where)->scope('where', $param)->page($page, 10)->select()->toArray();
        return arrString($lists);
    }

    /**
     * 获取运费模板信息
     * User: Jomlz
     * Date: 2020/8/11 20:37
     */
    public function getFreightTemplateInfo($param = [])
    {
        $template_id = $param['template_id'];
        $where = ['is_del'=>0,'template_id'=>$template_id];
        $model = new FreightTemplateModel();
        $freightTemplate = $model
            ->with(['freightConfig','freightConfig.freightRegion.region'])
            ->scope('where', $param)
            ->where($where)
            ->find();
        if ($freightTemplate){
            foreach ($freightTemplate['freightConfig'] as $key=>$val) {
                $area_ids = '';
                $area_names = '';
                foreach ($val['freightRegion'] as $k=>$v) {
                    $area_ids = $v->region->id . ',' .$area_ids;
                    $area_names = $v->region->name . ',' .$area_names;
                }
                $freightTemplate['freightConfig'][$key]['area_ids'] = trim($area_ids,',');
                $freightTemplate['freightConfig'][$key]['area_names'] = trim($area_names,',');
                unset($freightTemplate['freightConfig'][$key]['freightRegion']);
            }
        }
        return $freightTemplate;
    }


    /**
     * 运费模板信息操作
     * User: Jomlz
     * Date: 2020/8/11 17:42
     */
    public function freightTemplateHandle($act='',$param=[])
    {
        $arr = array_return();
        $handleLogic = new HandleLogic;
        $validate = new FreightTemplateValidate();
        $model = new FreightTemplateModel();
        $validate_result = $validate->scene($act)->check($param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        if ($act == 'edit' || $act == 'del'){
            $info = $model->where(['template_id' => $param['template_id']]) ->scope('where', $param)->find();
            if (!$info){
                return JsonUtils::fail('信息不存在');
            }
        }
        if ($act == 'del'){
            $confirm = $param['confirm'] ?? 0;
            $goods_count = Db::name('goods')->where(['template_id' => $param['template_id']])->count();
            if ($goods_count > 0 && $confirm == 0){
                $msg =  '已有' . $goods_count . '种商品使用该运费模板，确定删除该模板吗？继续删除将把使用该运费模板的商品设置成包邮。';
                return JsonUtils::fail($msg);
            };
            Db::name('goods')->where(['template_id' => $param['template_id']])->update(['template_id' => 0, 'is_free_shipping' => 1]);
            Db::name('freight_region')->where(['template_id' => $param['template_id']])->update(['is_del'=>1]);
            Db::name('freight_config')->where(['template_id' => $param['template_id']])->update(['is_del'=>1]);
            $param['is_del'] = 1;
            $act = 'edit';
            $arr = $handleLogic->Handle($param,'FreightTemplateModel',$act,'template_id');
            return return_json($arr);
        }
        $config_list = $param['config_list'] ? json_decode($param['config_list'],true) : [];
        $arr = $handleLogic->Handle($param,'FreightTemplateModel',$act,'template_id');
        $template_id = $arr['object_id'];
        $config_list_count = count($config_list);
        $config_id_arr = Db::name('freight_config')->where(['template_id' => $template_id])->column('config_id');
        $update_config_id_arr = [];
        if ($config_list_count > 0) {
            for ($i = 0; $i < $config_list_count; $i++) {
                $freight_config_data = [
                    'first_unit' => $config_list[$i]['first_unit'],
                    'first_money' => $config_list[$i]['first_money'],
                    'continue_unit' => $config_list[$i]['continue_unit'],
                    'continue_money' => $config_list[$i]['continue_money'],
                    'template_id' => $template_id,
                    'is_default' => $config_list[$i]['is_default'],
                ];
                if (empty($config_list[$i]['config_id'])) {
                    //新增配送区域
                    $config_id = Db::name('freight_config')->insertGetId($freight_config_data);
                    if(!empty($config_list[$i]['area_ids'])){
                        $area_id_arr = explode(',', $config_list[$i]['area_ids']);
                        if ($config_id !== false) {
                            foreach ($area_id_arr as $areaKey => $areaVal) {
                                Db::name('freight_region')->insert(['template_id'=>$template_id,'config_id' => $config_id, 'region_id' => $areaVal]);
                            }
                        }
                    }
                } else {
                    //更新配送区域
                    array_push($update_config_id_arr, $config_list[$i]['config_id']);
                    $config_result = Db::name('freight_config')->where(['config_id' => $config_list[$i]['config_id']])->update($freight_config_data);
                    if ($config_result !== false) {
                        Db::name('freight_region')->where(['config_id' => $config_list[$i]['config_id']])->delete();
                        if(!empty($config_list[$i]['area_ids'])){
                            $area_id_arr = explode(',', $config_list[$i]['area_ids']);
                            foreach ($area_id_arr as $areaKey => $areaVal) {
                                Db::name('freight_region')->insert(['template_id'=>$template_id,'config_id' => $config_list[$i]['config_id'], 'region_id' => $areaVal]);
                            }
                        }
                    }
                }
            }
        }
        $delete_config_id_arr = array_diff($config_id_arr, $update_config_id_arr);
        if (count($delete_config_id_arr) > 0) {
            Db::name('freight_region')->where(['config_id' => ['IN', $delete_config_id_arr]])->delete();
            Db::name('freight_config')->where(['config_id' => ['IN', $delete_config_id_arr]])->delete();
        }
        $this->checkFreightTemplate($template_id);
        return JsonUtils::successful();
    }

    /**
     * 检查模板，如果模板下没有配送区域配置，就删除该模板
     */
    private function checkFreightTemplate($template_id)
    {
        $freight_config =  Db::name('freight_config')->where(['template_id' => $template_id])->find();
        if (empty($freight_config)) {
            Db::name('freight_template')->where('template_id', $template_id)->delete();
        }
    }

    /**
     * 计算商品运费
     * User: Jomlz
     * Date: 2020/8/12 11:22
     */
    public function getFreight($goodsArr='', $region_id='')
    {
        $Goods = new GoodsModel();
        $freightLogic = new FreightLogic();
        $freightLogic->setRegionId($region_id);
        $goods_ids = get_arr_column($goodsArr, 'goods_id');
        $goodsList = $Goods->field('goods_id,volume,weight,template_id,is_free_shipping,identity_id')->where('goods_id', 'IN', $goods_ids)->select()->toArray();
        //记录商品数量
        foreach ($goodsList as $item=>$value){
            foreach ($goodsArr as $tt=>$vv){
                if ($value['goods_id'] == $vv['goods_id']) {
                    $goodsList[$item]['goods_num'] = $vv['goods_num'];
                }
            }
        }
        $total_freight = 0; //总运费
        $freight = 0;
        //为了不同平台类型来展示各自运费？
        $role_admin_array = array_group($goodsList,'identity_id');
        foreach ($role_admin_array as $key=>$value){
            $admin_array = $value;
            //同一个运费模板的商品放入同数组中
            $template_list = [];
            foreach ($admin_array as $goodsKey => $goodsVal) {
                $template_list[$goodsVal['template_id']][] = $goodsVal;
            }
            foreach ($template_list as $templateVal => $goodsArr) {
                $temp['template_id'] = $templateVal;
                $temp['total_volume'] = 0;
                $temp['total_weight'] = 0;
                $temp['goods_num'] = 0;
                foreach ($goodsArr as $goodsKey => $goodsVal) {
                    $temp['total_volume'] += $goodsVal['volume'] * $goodsVal['goods_num'];
                    $temp['total_weight'] += $goodsVal['weight'] * $goodsVal['goods_num'];
                    $temp['goods_num'] += $goodsVal['goods_num'];
                    $temp['is_free_shipping'] = $goodsVal['is_free_shipping'];
                }
                $freightLogic->setGoodsModel($temp);
                $freightLogic->setGoodsNum($temp['goods_num']);
                $freightLogic->doCalculation();
                $freight += $freightLogic->getFreight();
                unset($temp);
            }
            $total_freight += $freight;
            $role_admin_array[$key]['freight'] = $freight;
        }
        return $total_freight;
    }
}