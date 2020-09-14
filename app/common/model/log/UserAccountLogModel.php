<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: jomlz
 *           '::::::::::::::..                                   女神保佑，代码无bug！！！
 *                ..::::::::::::.                                Codes are far away from bugs with the goddess！！！
 *              ``::::::::::::::::
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
 * 用户账户日志model
 * Class UserAccountLogModel
 * @package app\common\model\log
 */
class UserAccountLogModel extends CommonModel
{
    protected $name = 'user_account_log';

    /**
     * 用户账户新增日志
     * @param $data
     * @return mixed
     */
    public function userAccountLogAdd($data){
        return $this->addInfo($data);
    }

}
