<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/8/10 11:33
 *                ..::::::::::::.                                女神保佑，代码无bug！！！
 *              ``::::::::::::::::                               Codes are far away from bugs with the goddess！！！
 *               ::::``:::::::::'        .:::.
 *              ::::'   ':::::'       .::::::::.
 *            .::::'      ::::     .:::::::'::::.
 *           .:::'       :::::  .:::::::::' ':::::.
 *          .::'        :::::.:::::::::'      ':::::.
 *         .::'         ::::::::::::::'         ``::::.
 *     ...:::           ::::::::::::'              ``::.
 *    ````':.          ':::::::::'                  ::::..
 *                       '.:::::'                    ':'````..
 *
 */
namespace app\common\model\log;

use app\common\model\CommonModel;

/**
 * 商品日志model
 * Class GoodsLogModel
 * @package app\common\model\log
 */
class GoodsLogModel extends CommonModel
{
    protected $name = 'goods_log';

    /**
     * 商品日志新增
     * @param $data
     * @return mixed
     */
    public function goodsLogAdd($data){
        return $this->addInfo($data);
    }

    /**
     * 商品日志删除
     * @param $log_id
     * @param $is_true
     * @return CommonModel|bool
     */
    public function goodsLogDeleteById($log_id,$is_true = true){
        return $this->deleteInfo(['id'=>$log_id],$is_true);
    }

}
