<?php


namespace app\common\model\user;


use app\common\model\CommonModel;
//用户余额下单记录，防止重复扣钱
class UserBalanceOrderLogModel extends CommonModel
{
protected $name='user_balance_order_log';
}