<?php


namespace app\common\logic\config;

//网站设置
use app\common\model\config\WebsiteConfigModel;
use sakuno\utils\JsonUtils;

class WebsiteConfigLogic
{
    /**
     * 网站设置详情
     * User: hao
     * Date: 2020/8/15
     */
    public function info($receive)
    {
        $websiteConfig = new WebsiteConfigModel();

//        $lists = $websiteConfig->getList(['config_type' => $receive], 'type,val,title,remarks');
        $lists = $websiteConfig->getColumn(['config_type' => $receive],'val','type');

        return JsonUtils::successful('操作成功', ['list'=>$lists]);
    }

    /**
     * 修改网站设置
     * User: hao
     * Date: 2020/8/15
     */
    public function edit($receive)
    {
        $websiteConfig = new WebsiteConfigModel();
        $lists = $websiteConfig->getList(['config_type' => $receive['config_type']], 'type,val');
        $vals = json_decode($receive['val'], true);

        try {
            $websiteConfig->beginTrans();
            foreach ($vals as $key => $value) {
                foreach ($lists as $k => $v) {
                    if ($key == $v['type'] && $value != $v['val']) {
                        $res = $websiteConfig->updateInfo(['type' => $key], ['val' => $value]);
                        if ($res === false) {
                            $websiteConfig->rollbackTrans();
                            return JsonUtils::fail('操作失败');
                        }
                    }
                }
            }
            $websiteConfig->commitTrans();
            return JsonUtils::successful('操作成功');
        } catch (\Exception $e) {
            $websiteConfig->rollbackTrans();
        }
    }
}