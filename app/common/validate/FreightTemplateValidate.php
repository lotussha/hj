<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/11
 * Time: 15:43
 */

namespace app\common\validate;

use think\Validate;

class FreightTemplateValidate extends Validate
{
    protected $rule = [
        'template_id|运费模板ID'      => 'require',
        'template_name|模板名称'      => 'require',
        'type|模板类型'       => 'number|gt:0',
    ];

    protected $scene = [
        'add'   => ['type','template_name'],
        'edit'   => ['template_id','type','template_name'],
        'del'   => ['template_id'],
        'info'   => ['template_id'],
    ];
}