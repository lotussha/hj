<?php


namespace app\common\model\user;


use app\common\model\CommonModel;

class UserAddressModel extends CommonModel
{
    protected $name = 'user_address';

    //可作为条件的字段
    protected $whereField = [
        'address_id',
    ];
}