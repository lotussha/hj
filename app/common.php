<?php
// 应用公共文件

use Firebase\JWT\JWT;
use think\Response;
use think\response\Json;
use think\facade\Db;


if (!function_exists('signToken')) {
    /**
     * 生成验签
     * @param $uid
     * @return string
     * User: Jomlz
     * Date: 2020/8/1 10:32
     */
    function signToken($uid)
    {
        $key = config('app.admin_token_key');         //这里是自定义的一个随机字串，应该写在config文件中的，解密时也会用，相当    于加密中常用的 盐  salt
        $token = array(
            "iss" => $key,        //签发者 可以为空
            "aud" => '',          //面象的用户，可以为空
            "iat" => time(),      //签发时间
            "nbf" => time(),    //在什么时候jwt开始生效  （这里表示生成100秒后才生效）
            "exp" => time() + 3600 * 5, //token 过期时间
            "data" => [           //记录的userid的信息，这里是自已添加上去的，如果有其它信息，可以再添加数组的键值对
                'role_type' => $uid,
                'admin_id' => $uid,
            ]
        );
        //  print_r($token);
        $jwt = JWT::encode($token, $key, "HS256");  //根据参数生成了 token
        return $jwt;
    }
}


if (!function_exists('checkToken')) {
    /**
     * 验证token
     * @param $token
     * @return array
     * User: Jomlz
     * Date: 2020/8/1 10:34
     */
    function checkToken($token)
    {
        $key = config('app.admin_token_key');
        $status = array("status" => 0);
        try {
            JWT::$leeway = 60;//当前时间减去60，把时间留点余地
            $decoded = JWT::decode($token, $key, array('HS256')); //HS256方式，这里要和签发的时候对应
            $arr = (array)$decoded;
            $res['status'] = 1;
            $res['data'] = $arr['data'];
            return $res;
        } catch (\Firebase\JWT\SignatureInvalidException $e) { //签名不正确
            $status['msg'] = "签名不正确";
            return $status;
        } catch (\Firebase\JWT\BeforeValidException $e) { // 签名在某个时间点之后才能用
            $status['msg'] = "token失效";
            return $status;
        } catch (\Firebase\JWT\ExpiredException $e) { // token过期
            $status['msg'] = "token失效";
            return $status;
        } catch (Exception $e) { //其他错误
            $status['msg'] = "未知错误";
            return $status;
        }
    }
}

if (!function_exists('getTree')) {
    //树结构
    function getTree($items, $pid = "parent_id")
    {
        $map = [];
        $tree = [];
        foreach ($items as &$it) {
            $map[$it['id']] = &$it;
        }  //数据的ID名生成新的引用索引树
        foreach ($items as &$at) {
            $parent = &$map[$at[$pid]];
            if ($parent) {
                $parent['children'][] = &$at;
            } else {
                $tree[] = &$at;
            }
        }
        return $tree;
    }
}

if (!function_exists('return_json')) {
    /**
     * 获取\think\response\Json对象实例
     * @param mixed $data 返回的数据
     * @param int $code 状态码
     * @param array $header 头部
     * @param array $options 参数
     * @return \think\response\Json
     */
    function return_json($data = [], $code = 200, $header = [], $options = []): Json
    {
        $data['msg'] = $data['code'] > 10000 ? codeMsg($data['code']) : $data['msg'];
        apiLog(var_export($data, true));
        return Response::create($data, 'json', $code)->header($header)->options($options);
    }
}
if (!function_exists('codeMsg')) {
    /**
     * 错误码
     * @param $code
     * @return mixed|string
     * User: Jomlz
     * Date: 2020/8/6 20:25
     */
    function codeMsg($code)
    {
        $codeMsg = array(
            '10000' => 'ok',
            '10001' => '接口不存在',
            '10002' => '请先登录',
            '10003' => '无权限',
            '10004' => '',
            '10005' => '',
            '10006' => '',
        );
        return isset($codeMsg[$code]) ? $codeMsg[$code] : '未知错误';
    }
}

if (!function_exists('update_stock_log')) {
    /**
     * 商品库存操作日志
     * @param int $muid 操作 用户ID
     * @param int $stock 更改库存数
     * @param array $goods 库存商品
     * @param string $order_sn 订单编号
     */
    function update_stock_log($muid, $stock = 1, $goods = [], $order_sn = '')
    {
        $data['ctime'] = time();
        $data['stock'] = $stock;
        $data['muid'] = $muid;
        $data['goods_id'] = $goods['goods_id'];
        $data['goods_name'] = $goods['goods_name'];
        $data['goods_spec'] = empty($goods['spec_key_name']) ? $goods['key_name'] : $goods['spec_key_name'];
        $data['order_sn'] = $order_sn;
        Db::name('log_stock')->insert($data);

    }
}
if (!function_exists('get_arr_column')) {
    /**
     * 获取数组中的某一列
     * @param array $arr 数组
     * @param string $key_name 列名
     * @return array  返回那一列的数组
     */
    function get_arr_column($arr, $key_name)
    {
        $arr2 = array();
        foreach ($arr as $key => $val) {
            $arr2[] = $val[$key_name];
        }
        return $arr2;
    }
}
if (!function_exists('array_group')) {
    /**
     * 数组根据相同字段再分组
     * @param $arr
     * @param $key
     * @return array
     * User: Jomlz
     */
    function array_group($arr, $key)
    {
        $result = []; //初始化一个数组
        foreach ($arr as $k => $v) {
            $result[$v[$key]][] = $v; //把$key对应的值作为键 进行数组重新赋值
        }
        return array_values($result);
    }
}

if (!function_exists('getIntMicrotime')) {
    /**
     * 获取毫秒数
     * @return
     * User: hao
     */
    function getIntMicrotime()
    {
        $start = microtime(true);
        usleep(rand(300, 600));
        $start = explode('.', $start);
        $start[0] = $start[0] . '000';
        $wTime = intval($start[0] + $start[1]);
        return $wTime;
    }
}

if (!function_exists('EmojiEncode')) {
    /**
     *  [userTextEncode  特殊符号和emoji表情 ] 编码  (获取微信数据放数据库里面)
     * @return
     * User: hao
     */
    function EmojiEncode($str)
    {
        if (!is_string($str)) return $str;
        if (!$str) return '';
        $text = json_encode($str); //暴露出unicode
        $text = preg_replace_callback("/(\\\u[ed][0-9a-f]{3})/i", function ($str) {
            return addslashes($str[0]);
        }, $text); //将emoji的unicode留下，其他不动，这里的正则比原答案增加了d，因为我发现我很多emoji实际上是\ud开头的，反而暂时没发现有\ue开头。
        return json_decode($text);
    }
}

if (!function_exists('EmojiDecode')) {
    /**
     * 特殊符号和emoji表情 转义解码（ 拿数据库数据转义 ）
     * @return
     * User: hao
     */
    function EmojiDecode($str)
    {
        $text = json_encode($str); //暴露出unicode
        $text = preg_replace_callback('/\\\\\\\\/i', function ($str) {
            return '\\';
        }, $text); //将两条斜杠变成一条，其他不动
        return json_decode($text);
    }
}

if (!function_exists('EmojiEncodeon')) {
    /**
     * 过滤表情,用户输入表情返回 false
     * @return
     * User: hao
     */
    function EmojiEncodeon($str)
    {
        $a = $str;
        if (!is_string($str)) return $str;
        if (!$str) return '';

        $text = json_encode($str); //暴露出unicode

        $text = preg_replace_callback("/(\\\u[ed][0-9a-f]{3})/i", function ($str) {
            return addslashes($str[0]);
        }, $text); //将emoji的unicode留下，其他不动，这里的正则比原答案增加了d，因为我发现我很多emoji实际上是\ud开头的，反而暂时没发现有\ue开头。

        $b = json_decode($text);
        if ($a != $b) {
            return false;
        } else {
            return $a;
        }
    }
}

if (!function_exists('get_downline')) {
    /**
     * 获取无限下级 二维数组
     * @return
     * User: hao
     */
    function get_downline($array, $pid = 0, $parent_id = 'parent_id', $level = 1)
    {

        //声明静态数组,避免递归调用时,多次声明导致数组覆盖
        static $list = [];
        foreach ($array as $key => $value) {

            //第一次遍历,找到父节点为根节点的节点 也就是pid=0的节点
            if ($value[$parent_id] == $pid) {
                //父节点为根节点的节点,级别为0，也就是第一级
                $value['level'] = $level;
                //把数组放到list中
                $list[] = $value;
                //把这个节点从数组中移除,减少后续递归消耗
                unset($array[$key]);
                //开始递归,查找父ID为该节点ID的节点,级别则为原级别+1
                get_downline($array, $value['id'], $parent_id, $level + 1);
            }
        }
        return $list;
    }

}
if (!function_exists('kuaidi100')) {
    /**
     * 快递查询
     * @param $expressName  快递名称
     * @param $number   单号
     * @return mixed
     */
    function kuaidi100($expressName, $number)
    {
        http://api.kuaidi100.com/api?id=[]&com=[]&nu=[]&valicode=[]&show=[0|1|2|3]&muti=[0|1]&order=[desc|asc]
        $host = "http://www.kuaidi100.com";
        $path = "/query";
        $method = "GET";
//    $key = "31378bc9581e4ee79bbc705283ff594c";
        $headers = array();
        $querys = "type=$expressName&postid=$number";
        $bodys = "";
        $url = $host . $path . "?" . $querys;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);;//返回response头部信息
        if (1 == strpos("$" . $host, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $info = curl_exec($curl);
        return $info;
    }
}

if (!function_exists('apiLog')) {
    /**
     * 通用日志
     * @param $text 内容
     * User: Jomlz
     * Date: 2020/7/31 16:34
     */
    function apiLog($text)
    {
        $max_size = 500000;
        $log_filename = date("Y-m") . '.log';
        if (file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)) {
            unlink($log_filename);
        }
        file_put_contents($log_filename, date("Y-m-d H:i:s") . microtime() . " \r\n " . toJson($text) . "\r\n", FILE_APPEND);
    }
}

if (!function_exists('toJson')) {
    /**
     * @param $data
     * @return false|string|string[]|null
     * User: Jomlz
     * Date: 2020/7/31 16:35
     */
    function toJson($data)
    {
        if (!empty ($data)) {
            $fileType = mb_detect_encoding($data, array('UTF-8', 'GBK', 'GB2312', 'LATIN1', 'BIG5'));
            if ($fileType != 'UTF-8') {
                $data = mb_convert_encoding($data, 'UTF-8', $fileType);
            }
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
        return array('status' => "1", 'code' => "10000", 'msg' => 'ok', 'data' => (object)array());
    }
}
if (!function_exists('turnString')) {
    function turnString($obj)
    {
        foreach ($obj as $k => $v) {
            if (empty($v) && $v != 0) {
                $obj[$k] = "";
            }
            if (is_array($v) && empty($v)) {
                $obj[$k] = array();
            }
            if (is_null($v) == 1) {
                $obj[$k] = "";
            }
            if (is_int($v) == 1 || is_double($v) == 1) {
                settype($v, 'string');
                $obj[$k] = $v;
            }
        }
        return $obj;
    }
}

if (!function_exists('arrString')) {
    function arrString($obj)
    {
        if (is_array($obj) == 1) {
            foreach ($obj as $key => $val) {
                $val = turnString($val);
                $obj[$key] = $val;
            }
        }
        return $obj;
    }
}

if (!function_exists('uniqueNumber')) {
    /**
     * 生成30位随机订单号
     * @return
     * User: hao
     */
    function uniqueNumber()
    {
        //14 . 5 . 5 . 6
        $out_trade_no = date('YmdHis') . str_shuffle(substr(time(), -5)) . substr(microtime(), 2, 5) . rand(100000, 999999);
        return $out_trade_no;
    }
}

if (!function_exists('flash_sale_time_space')) {
    function flash_sale_time_space()
    {
        $now_day = date('Y-m-d');
        $now_time = date('H');
//        if ($now_time % 2 == 0) {
//            $flash_now_time = $now_time;
//        } else {
//            $flash_now_time = $now_time - 1;
//        }
        $flash_now_time = $now_time;
        $flash_sale_time = strtotime($now_day . " " . $flash_now_time . ":00:00");
        $space = 3600;
        $time_space = [
            '1' => array('font' => date("H:i", $flash_sale_time), 'start_time' => $flash_sale_time, 'end_time' => $flash_sale_time + $space),
            '2' => array('font' => date("H:i", $flash_sale_time + $space), 'start_time' => $flash_sale_time + $space, 'end_time' => $flash_sale_time + 2 * $space),
            '3' => array('font' => date("H:i", $flash_sale_time + 2 * $space), 'start_time' => $flash_sale_time + 2 * $space, 'end_time' => $flash_sale_time + 3 * $space),
            '4' => array('font' => date("H:i", $flash_sale_time + 3 * $space), 'start_time' => $flash_sale_time + 3 * $space, 'end_time' => $flash_sale_time + 4 * $space),
            '5' => array('font' => date("H:i", $flash_sale_time + 4 * $space), 'start_time' => $flash_sale_time + 4 * $space, 'end_time' => $flash_sale_time + 5 * $space),
            '6' => array('font' => date("H:i", $flash_sale_time + 5 * $space), 'start_time' => $flash_sale_time + 5 * $space, 'end_time' => $flash_sale_time + 6 * $space),
        ];
        return $time_space;
    }
}

if (!function_exists('curlHttp')) {
    /**
     * curlHttp curl请求封装
     * @return
     * User: hao
     */
    function curlHttp($url, $type = "GET", $data = '', $decode = true)
    {

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

        if (strtoupper($type) == 'POST') {

            curl_setopt($curl, CURLOPT_POST, 1);

            curl_setopt($curl, CURLOPT_POSTFIELDS, is_array($data) ? json_encode($data) : $data);

            if (is_array($data)) {
                $header[] = "Content-Type:application/json;charset=UTF-8";
            }

        }

        if (@$header) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        // dump($result );exit;

        return $decode ? json_decode($result, true) : $result;
    }

}

if (!function_exists('apiSignToken')) {
    /**
     * 生成验签
     * @param $uid
     * @return string
     * User: hao
     * Date: 2020/8/21
     */
    function apiSignToken($uid)
    {
        $key = config('app.api_token_key');         //这里是自定义的一个随机字串，应该写在config文件中的，解密时也会用，相当    于加密中常用的 盐  salt
        $token = array(
            "iss" => $key,        //签发者 可以为空
            "aud" => '',          //面象的用户，可以为空
            "iat" => time(),      //签发时间
            "nbf" => time(),    //在什么时候jwt开始生效  （这里表示生成100秒后才生效）
            "exp" => time() + 3600 * 24 * 5, //token 过期时间
            "data" => [           //记录的userid的信息，这里是自已添加上去的，如果有其它信息，可以再添加数组的键值对
                'api_id' => $uid,
            ]
        );
        //  print_r($token);
        $jwt = JWT::encode($token, $key, "HS256");  //根据参数生成了 token
        return $jwt;
    }
}

if (!function_exists('apiCheckToken')) {
    /**
     * 验证token
     * @param $token
     * @return array
     * User: hao
     * Date: 2020/8/21
     */
    function apiCheckToken($token)
    {
        $key = config('app.api_token_key');
        $status = array("status" => 0);
        try {
            JWT::$leeway = 60;//当前时间减去60，把时间留点余地
            $decoded = JWT::decode($token, $key, array('HS256')); //HS256方式，这里要和签发的时候对应
            $arr = (array)$decoded;
            $res['status'] = 1;
            $res['data'] = $arr['data'];
            return $res;
        } catch (\Firebase\JWT\SignatureInvalidException $e) { //签名不正确
            $status['msg'] = "签名不正确";
            return $status;
        } catch (\Firebase\JWT\BeforeValidException $e) { // 签名在某个时间点之后才能用
            $status['msg'] = "token失效";
            return $status;
        } catch (\Firebase\JWT\ExpiredException $e) { // token过期
            $status['msg'] = "token失效";
            return $status;
        } catch (Exception $e) { //其他错误
            $status['msg'] = "未知错误";
            return $status;
        }
    }
}

if (!function_exists('getRandomStr')) {
    /**
     * 获取指定位数的随机字符串,可用于验证码等
     * @return array
     * User: hao
     * Date: 2020/8/21
     */
    function getRandomStr($num = 4)
    {
        $str = "812153123939874126623832423343295646465448787912338897516565441652341421324321564123";
        $params = substr(str_shuffle($str . time()), 0, $num);
        return $params;
    }
}

if (!function_exists('isImg')) {
    /**
     * 检查图片是否合法
     * hao 2020.08.29
     * */
    function isImg($img)
    {
        $img = explode(',', $img);
        $types = 'gif|jpeg|png|bmp|jpg';  //定义检查的图片类型
        $types = explode('|', $types);

        $arr = array();
        foreach ($img as $k => $val) {
            $mimetype = @end(explode(".", $val));
            $is = 0;
            foreach ($types as $key => $value) {
                if ($mimetype == $value) {
                    $arr[] = $val;
                    $is = 1;
                }
            }
            if ($is == 1) {
                continue;
            }
            return false;
        }


        if ($arr) {
            $arr = implode(',', $arr);
        } else {
            $arr = false;
        }
        return $arr;

    }
}


if (!function_exists('format_date')) {
    /**
     * $time 时间戳
     * 几分钟前、几小时前、几天前
     * hao 2020.09-01
     * */

    function format_date($time)
    {
        if (!is_int($time)) {
            $time = strtotime($time);
        }
        $t = time() - $time;
        $f = array(
            '31536000' => '年',
            '2592000' => '个月',
            '604800' => '星期',
            '86400' => '天',
            '3600' => '小时',
            '60' => '分钟',
            '1' => '秒'
        );
        foreach ($f as $k => $v) {
            if (0 != $c = floor($t / (int)$k)) {
                return $c . $v . '前';
            }
        }
    }
}

