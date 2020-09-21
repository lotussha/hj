<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/8/5 11:50
 *                ..::::::::::::.                                女神保佑，代码无bug！！！
 *              ``::::::::::::::::                               Codes are far away from bugs with the goddess！！！
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
namespace sakuno\utils;

use think\Response;

define('SUCCESS',10000); // 成功通用
define('ERROR',00000); // 失败通用
/* 参数错误：10001-19999 */
define('PARAM_IS_INVALID',10001); // 参数异常
define('PARAM_IS_BLANK',10002); // 参数为空
define('PARAM_TYPE_BIND_ERROR',10003); // 参数类型错误
define('PARAM_NOT_COMPLETE',10004); // 参数缺失
/* 用户错误：20001-29999*/
define('USER_NOT_LOGGED_IN',20001); // 用户未登录
define('USER_LOGIN_ERROR',20002); // 账号不存在或密码错误
define('USER_ACCOUNT_FORBIDDEN',20003); // 账号已被禁用
define('USER_NOT_EXIST',20004); // 用户不存在
define('USER_HAS_EXISTED',20005); // 用户已存在
define('USER_NOT_BINGING',20006); // 用户未绑定
define('USER_TOKEN_TIMEOUT',20007); // 授权已过期
define('TOKEN_ERROR', 20008); // 签名错误

/* 业务错误：30001-39999 */
define('SPECIFIED_QUESTIONED_USER_NOT_EXIST',30001); //业务错误
/* 系统错误：40001-49999 */
define('SPECIFIED_QUESTIONED_USER_NOT_EXIST_DEFINE',40001); //系统繁忙，请稍后重试
/* 数据错误：50001-599999 */
define('RESULE_DATA_NONE',50001); // 数据未找到
define('DATA_IS_WRONG',50002); // 数据异常
define('DATA_ALREADY_EXISTED',50003); // 数据已存在
/* 接口错误：60001-69999 */
define('INTERFACE_INNER_INVOKE_ERROR',60001); // 内部系统接口调用异常
define('INTERFACE_OUTTER_INVOKE_ERROR',60002); // 外部系统接口调用异常
define('INTERFACE_FORBID_VISIT',60003); // 该接口禁止访问
define('INTERFACE_ADDRESS_INVALID',60004); // 接口地址无效
define('INTERFACE_REQUEST_TIMEOUT',60005); // 接口请求超时
define('INTERFACE_EXCEED_LOAD',60006); // 接口负载过高
/* 权限错误：70001-79999 */
define('PERMISSION_NO_ACCESS',70001); // 无访问权限




/**
 * 返回json数据静态工具类
 * Class JsonUtils
 * @package app\sakuno\utils
 */
class JsonUtils
{

    /**
     * 定义响应code
     * @var int
     */
    static private $httpCode = 200;

    public function httpCode(int $httpCode): self
    {
        self::$httpCode = $httpCode;
        return $this;
    }

    /**
     * 构建返回对象
     * @param int $status
     * @param string $msg
     * @param array|null $data
     * @param string|null $code
     * @param array|null $other
     * @return Response
     */
    static public function make(int $status, string $msg, ?array $data = [], int $code = null, ?array $other = null):Response
    {
        $data = (object)$data;
        $res = compact('status', 'msg');
        $res['code'] = $code ?? SUCCESS;
        $res['data'] = $data;
        $res = turnString($res); //调整字符串
        if(!is_null($other))
            foreach($other as $k => $v){
                $res[$k] = $v;
            }
        apiLog(var_export($data, true));
        return Response::create($res, 'json', self::$httpCode);
    }

    /**
     * 响应成功数据
     * @param string $message
     * @param array|null $data
     * @param string|null $code
     * @return Response
     */
    static public function successful($message = 'ok', ?array $data = [], int $code = null):Response
    {
        if (is_array($message)) {
            $data = $message;
            $message = 'ok';
        }

        return self::make(config('status.success_status'), $message, $data, $code);
    }

    /**
     * 业务失败数据
     * @param string $message
     * @param int|null $code
     * @param array|null $data
     * @return Response
     */
    static public function fail($message = 'fail',int $code = null,?array $data = []):Response
    {
        if (is_array($message)) {
            $data = $message;
            $message = 'fail';
        }
        return self::make(config('status.error_status'), $message, $data,$code);
    }

    /**
     * 返回Exception响应
     * @param string $message
     * @param array|null $data
     * @param string|null $code
     * @param int $httpStatus
     * @return Response
     */
    static public function showException($message = 'fail', ?array $data = null, int $code = null, $httpStatus = 500):Response
    {
        if (is_array($message)) {
            $data = $message;
            $message = 'fail';
        }
        self::$httpCode = $httpStatus;
        return self::make(config('status.error_status'), $message, $data,$code);
    }

    /**
     * 方法找不到响应
     * @param string $message
     * @param array|null $data
     * @param string|null $code
     * @param int $httpStatus
     * @return Response
     */
    static public function showNotFound($message = 'fail', ?array $data = null, int $code = null, $httpStatus = 404):Response
    {
        if (is_array($message)) {
            $data = $message;
            $message = 'fail';
        }
        self::$httpCode = $httpStatus;
        return self::make(config('status.action_not_found'), $message, $data,$code);
    }

    /**
     * 返回成功数据
     * @param string $message
     * @param array|null $data
     * @param int|null $code
     * @return array
     */
    static public function returnDataSuc(string $message = 'ok',int $code = null, ?array $data = []){
        $code = $code ?? SUCCESS;
        return ['status' => 0,'msg'=>$message,'data'=>$data,'code'=>$code];
    }

    /**
     * 返回失败数据
     * @param string $message
     * @param array|null $data
     * @param int|null $code
     * @return array
     */
    static public function returnDataErr(string $message = 'fail',int $code = null, ?array $data = []){
        $code = $code ?? ERROR;
        return ['status' => 1,'msg'=>$message,'data'=>$data,'code'=>$code];
    }


}