<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * 门店财务
 */

namespace app\common\model\shop;

use app\common\model\CommonModel;
use app\common\model\settlement\SettlementModel;

class ShopFinanceModel extends CommonModel
{
    protected $name = 'shop_finance';

    public function shopInfo()
    {
        return $this->hasOne(SettlementModel::class,'admin_id','identity_id');
    }
}