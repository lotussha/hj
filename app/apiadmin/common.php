<?php
use \app\common\Loader;
use think\exception\HttpResponseException;

if (!function_exists('model')) {
    /**
     * 实例化Model
     * @param string $name Model名称
     * @param string $layer 业务层名称
     * @param bool $appendSuffix 是否添加类名后缀
     * @return \think\Model
     */
}
function model($name = '', $layer = 'model', $appendSuffix = false, $module = 'apiadmin')
{
    return Loader::model($name, $layer, $appendSuffix, $module);
}

if (!function_exists('apiLog')) {
    /**
     * 通用日志
     * @param $text 内容
     * User: Jomlz
     * Date: 2020/7/31 16:34
     */
    function apiLog($text) {
        $max_size = 500000;
        $log_filename = date("Y-m").'.log';
        if(file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)){unlink($log_filename);}
        file_put_contents ($log_filename, date("Y-m-d H:i:s").microtime()." \r\n ".toJson( $text). "\r\n", FILE_APPEND );
    }
}

if (!function_exists('toJson')) {
    /**
     * @param $data
     * @return false|string|string[]|null
     * User: Jomlz
     * Date: 2020/7/31 16:35
     */
    function toJson($data) {
        if (! empty ( $data )) {
            $fileType = mb_detect_encoding ( $data, array ('UTF-8','GBK','GB2312','LATIN1','BIG5') );
            if ($fileType != 'UTF-8') {$data = mb_convert_encoding ( $data, 'UTF-8', $fileType );}
        }
        return $data;
    }
}
if (!function_exists('array_return')) {
    /**
     * 通用接口返回规范
     * @return array
     * User: Jomlz
     * Date: 2020/7/31 16:49
     */
    function array_return()
    {
        return array('status' => "1",'code'=>"10000",'msg' => 'ok', 'data' => (object)array());
    }
}
if (!function_exists('turnString')) {
    function turnString($obj){
        foreach ($obj as $k=>$v){
            if(empty($v) && $v != 0){
                $obj[$k] = "";
            }
            if(is_array($v) && empty($v)){
                $obj[$k] = array();
            }
            if(is_null($v) == 1){
                $obj[$k] = "";
            }
            if(is_int($v) == 1 || is_double($v) == 1){
                settype($v,'string');
                $obj[$k] = $v;
            }
        }
        return $obj;
    }
}

if (!function_exists('arrString')) {
    function arrString($obj){
        if (is_array($obj) == 1){
            foreach ($obj as $key=>$val){
                $val = turnString($val);
                $obj[$key] = $val;
            }
        }
        return $obj;
    }
}

if (!function_exists('exception')) {
    /**
     * 抛出异常处理
     *
     * @param string $msg 异常消息
     * @param integer $code 异常代码 默认为0
     * @param string $exception 异常类
     *
     * @throws Exception
     */
    function exception($msg, $code = 0, $exception = '')
    {
        $e = $exception ?: '\think\Exception';
        throw new $e($msg, $code);
    }
}



