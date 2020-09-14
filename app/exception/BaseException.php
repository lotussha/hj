<?php


namespace app\exception;


use Throwable;

class BaseException extends \Exception
{
    public $code = 400;
    public $msg = '参数错误';
    public $error_code = 10000;  // 自定义错误码

    public function __construct($params = [])
    {
        if (!is_array($params)) {
            return;
        }
        if (array_key_exists('code', $params)) {
            $this->code = $params['code'];
        }
        if (array_key_exists('msg', $params)) {
            $this->msg = $params['msg'];
        }
        if (array_key_exists('error_code', $params)) {
            $this->error_code = $params['error_code'];
        }
    }
}