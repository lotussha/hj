<?php


namespace app\exception;


class OrderStatusException extends BaseException
{
    public $code = 200;

    public $msg = '订单已付款, 不能修改金额';

    public $error_code = 10000;
}