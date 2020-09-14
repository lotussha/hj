<?php


namespace app\exception;


class OrderCancelException extends BaseException
{
    public $code = 200;

    public $msg = '订单当前状态不能取消';

    public $error_code = 10001;
}