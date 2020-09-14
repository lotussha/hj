<?php


namespace app\exception;


class NotDataException extends BaseException
{
    public $code = 200;

    public $msg = '你查询的数据不存在';

    public $error_code = 10000;
}