<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/11
 * Time: 15:40
 * 运费模板
 */

namespace app\apiadmin\controller\freight;

use app\apiadmin\controller\Base;
use app\common\logic\FreightTemplateLogic;
use app\common\validate\FreightTemplateValidate;
use sakuno\utils\JsonUtils;

class FreightTemplate extends Base
{
    /**
     * 运费模板列表
     * @param FreightTemplateLogic $freightTemplateLogic
     * @return \think\response\Json
     * User: Jomlz
     * Date: 2020/8/11 20:52
     */
    public function lists(FreightTemplateLogic $freightTemplateLogic)
    {
        $lists = $freightTemplateLogic->freightTemplateLists($this->param);
        $data = ['lists'=>$lists];
        return JsonUtils::successful('获取成功',$data);
    }

    /**
     * 运费模板信息
     * User: Jomlz
     * Date: 2020/8/11 20:51
     */
    public function freight_info(FreightTemplateValidate $validate,FreightTemplateLogic $logic)
    {
        $validate_result = $validate->scene('info')->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($validate->getError());
        }
        $info = $logic->getFreightTemplateInfo($this->param);
        if (!$info){
            return JsonUtils::fail('信息不存在');
        }
        $response['info'] = turnString($info->toArray());
        return JsonUtils::successful('获取成功',$response);
    }

    public function freight_add(FreightTemplateLogic $freightTemplateLogic)
    {
        return $freightTemplateLogic->freightTemplateHandle('add',$this->param);

    }
    public function freight_edit(FreightTemplateLogic $freightTemplateLogic)
    {
        return $freightTemplateLogic->freightTemplateHandle('edit',$this->param);
    }

    public function freight_del(FreightTemplateLogic $freightTemplateLogic)
    {
        return $freightTemplateLogic->freightTemplateHandle('del',$this->param);
    }
}