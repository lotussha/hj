<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * 系统记录日志
 */

namespace app\common\model\log;

use app\common\model\CommonModel;

class SyslogsModel extends CommonModel
{
    protected $name = 'log_syslogs';

    /**
     * 增加系统日志
     * @param $data
     * @return mixed
     */
    public function addLog($data)
    {
        return $this->addInfo($data);
    }
}