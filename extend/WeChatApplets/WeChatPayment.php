<?php


namespace WeChatApplets;


use app\common\model\config\WebsiteConfigModel;


//微信支付
class WeChatPayment
{

    /**
     * SDK版本号
     * @var string
     */
    public static $VERSION = "3.0.10";

    protected $appid = ''; //微信小程序的唯一id
    protected $appSecret = ''; //微信小程序的密钥
    protected $mch_id = ''; //商户号
    protected $mch_secret = ''; //商户密钥
    protected $sign_type = 'MD5'; //签名类型MD5,默认支持MD5，请勿随意修改！
//    protected $trade_type = 'JSAPI'; //交易类型
    protected $appAppid = ''; //app微信小程序的唯一id
    protected $appMch_id = '';//app商户号



    //构造方法
    public function __construct()
    {
        $website = new WebsiteConfigModel();
        $condition = 'wxmch_id,wxmch_secret,xiaoappid,app_secret,app_appid,appmch_id';
        $website_list = $website->where('type', 'in', $condition)->field('type,val')->column('val', 'type');
        $this->appid = $website_list['xiaoappid'];  //微信小程序的唯一id
        $this->appSecret = $website_list['app_secret'];  //微信小程序的密钥
        $this->mch_id = $website_list['wxmch_id'];  //商户号
        $this->mch_secret = $website_list['wxmch_secret'];   //商户密钥
        $this->appAppid = $website_list['app_appid'];  //app微信小程序的唯一id
        $this->appMch_id = $website_list['appmch_id'];  //app商户号
//        $this->appid = 'wxb8b070f331703f6d';  //微信小程序的唯一id
//        $this->appSecret = ;  //微信小程序的密钥
//        $this->mch_id ='1573142041';  //微信小程序商户号
//        $this->mch_secret = 'ASJDFPOUE09E1284E09SJDOPSAJRPOIE';   //商户密钥
//        $this->appAppid ='wx7af999f248ac6ec0';  //app微信小程序的唯一id
//        $this->appMch_id = '1573142041';  //app商户号
//        dump($this->appid);
//        dump($this->mch_id);
//        dump($this->mch_secret);exit();
    }


    /**
     *
     * 小程序统一下单，WxPayUnifiedOrder中out_trade_no、body、total_fee、trade_type必填
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param order_id $order_id 唯一订单号
     * @param body $body 商品描述
     * @param price $price 商品价格
     * @param openid $openid 用户的唯一的openid
     * @return 成功时返回，其他抛异常
     * @throws WxPayException
     */
    public function appletsUnifiedOrder($order_id = '', $body = '', $price = '0.00', $openid = '',$notify_url='')
    {
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder"; //微信支付的统一下单地址
        $data['appid'] = $this->appid; //小程序APPID
        $data['mch_id'] = $this->mch_id; //商户号
        $data['out_trade_no'] = $order_id; //平台唯一订单号
        $data['body'] = $body; //商品描述
        $data['nonce_str'] = $this->getNonceStr(); //随机字符串32位
        $data['total_fee'] = round($price, 2) * 100; //支付金额，单位为分
        $data['spbill_create_ip'] = $_SERVER['REMOTE_ADDR']; //获取终端ip地址
        $data['notify_url'] = $notify_url; //通知地址
        $data['trade_type'] = 'JSAPI'; //支付类型
        $data['openid'] = $openid; //小程序用户的唯一openid
        $data['sign'] = $this->MakeSign($data); //生成签名
        $xml = $this->ToXml($data); //生成XML格式
        if ($xml === false){
            return '非法XML！';
        }

        $xmlData = $this->postXmlCurl($url,$xml); //发起curl请求

        $result = $this->FromXml($xmlData);//xml转化为数组形式
        if ( @$result['return_code'] == 'SUCCESS' && @$result['result_code'] == 'SUCCESS' ) {

            $signData['appId'] = $this->appid; //小程序Id
            $signData['timeStamp'] = "'".time()."'"; //当前时间戳
            $signData['nonceStr'] = $result['nonce_str']; //随机字符串
            $signData['package'] = 'prepay_id='.$result['prepay_id']; //微信那边生成的订单号
            $signData['signType'] = 'MD5';
            $signData['paySign'] = $this->MakeSign($signData); //生成签名
            return $signData;
        }
        return '付款订单异常01！';
    }


    /**
     *
     * APP发起微信统一下单，WxPayUnifiedOrder中out_trade_no、body、total_fee、trade_type必填
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param order_id $order_id  唯一订单号
     * @param body $body 商品描述
     * @param price $price 商品价格
     * @param notify_url $notify_url 回调地址
     * @throws WxPayException
     * @return 成功时返回，其他抛异常
     */
    public function appWxPay($order_id,$body,$price,$notify_url='')
    {
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder"; //微信支付的统一下单地址
        $data['appid'] = $this->appAppid; //小程序APPID
        $data['mch_id'] = $this->appMch_id; //商户号
        $data['out_trade_no'] = $order_id; //平台唯一订单号
        $data['body'] = $body; //商品描述
        $data['nonce_str'] = $this->getNonceStr(); //随机字符串32位
        $data['total_fee'] = round($price,2)*100; //支付金额，单位为分
        $data['spbill_create_ip'] = $_SERVER['REMOTE_ADDR']; //获取终端ip地址
        $data['notify_url'] = $notify_url; //回调url
        $data['trade_type'] = 'APP'; //支付类型
        $data['sign'] = $this->MakeSign($data); //生成签名

        $xml = $this->ToXml($data); //生成XML格式
        if ($xml === false) {
            return '非法XML！';
        };

        $xmlData = $this->postXmlCurl($url,$xml); //发起curl请求
        $result = $this->FromXml($xmlData);//xml转化为数组形式
        if ( @$result['return_code'] == 'SUCCESS' && @$result['result_code'] == 'SUCCESS' ) {
            $signData['appId'] = $this->appAppid; //APPid
            $signData['timeStamp'] = "'".time()."'"; //当前时间戳
            $signData['nonceStr'] = $result['nonce_str']; //随机字符串
            $signData['package'] = 'prepay_id='.$result['prepay_id']; //微信那边生成的订单号
            $signData['signType'] = 'MD5';
            $signData['paySign'] = $this->MakeSign($signData); //生成签名
            return $signData;
        }

        return '付款订单异常01';
    }



    /**
     * 生成签名
     * @param WxPayConfigInterface $config  配置对象
     * @param bool $needSignType  是否需要补signtype
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function MakeSign($array, $signType="MD5")
    {

        //签名步骤一：按字典序排序参数
        ksort($array);
        $string = $this->ToUrlParams($array);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".$this->mch_secret;

        //签名步骤三：MD5加密或者HMAC-SHA256
        if( $signType == "MD5" ){
            $string = md5($string);
        } else if($signType == "HMAC-SHA256") {
            $string = hash_hmac( "sha256",$string ,$this->mch_secret );
        } else {
            // throw new WxPayException("签名类型不支持！");
            return false;
        }

        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 输出xml字符
     * @throws WxPayException
     **/
    public function ToXml($array)
    {
        if ( !is_array($array) || count($array) <= 0 ) {
            // $xmlData['code'] = 403;
            // $xmlData['msg'] = '数组数据异常！';
            // return $xmlData;
            // throw new WxPayException("数组数据异常！");
            return false;
        }

        $xml = "<xml>";
        foreach ($array as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    public function FromXml($xml)
    {
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $result;
    }

    /**
     * 格式化参数格式化成url参数
     */
    protected function ToUrlParams($arr)
    {
        $buff = "";

        foreach ($arr as $k => $v)
        {
            if ($k != "sign" && $v != "" && !is_array($v))
            {
                $buff .= $k . "=" . $v . "&";
            }
        }
        //trim()移除字符串两侧的字符
        $buff = trim($buff, "&");
        return $buff;
    }


    /**
     * 以post方式提交xml到对应的接口url
     *
     * @param WxPayConfigInterface $config  配置对象
     * @param string $xml  需要post的xml数据
     * @param string $url  url
     * @param bool $useCert 是否需要证书，默认不需要
     * @param int $second   url执行超时时间，默认30s
     * @throws WxPayException
     */
    private static function postXmlCurl( $url,$xml,$second = 30,$useCert = false)
    {

        $ch = curl_init();

        // $curlVersion = curl_version();
        // $ua = "WXPaySDK/".self::$VERSION." (".PHP_OS.") PHP/".PHP_VERSION." CURL/".$curlVersion['version']." "
        // .$this->mch_id();

        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);


        // $proxyHost = "0.0.0.0";
        // $proxyPort = 0;
        // //如果有配置代理这里就设置代理
        // if($proxyHost != "0.0.0.0" && $proxyPort != 0){
        // 	curl_setopt($ch,CURLOPT_PROXY, $proxyHost);
        // 	curl_setopt($ch,CURLOPT_PROXYPORT, $proxyPort);
        // }

        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验

        // curl_setopt($ch,CURLOPT_USERAGENT, $ua);

        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if ($useCert == true) {
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            //证书文件请放入服务器的非web目录下
            $sslCertPath = EXTEND_PATH.'cert/apiclient_cert.pem'; //证书路径
            $sslKeyPath = EXTEND_PATH.'cert/apiclient_key.pem'; //证书路径
            // $config->GetSSLCertPath($sslCertPath, $sslKeyPath);
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, $sslCertPath);
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, $sslKeyPath);
        }

        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            return false;
            // throw new WxPayException("curl出错，错误码:$error");
        }
    }


    /**
     *
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return 产生的随机字符串
     */
    public static function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }


    /**
     * [orderQuery 向微信那边查询订单并且校验此订单是否已支付]
     * @param  string  $order_id  [自己平台的订单号]
     * @param  boolean $total_fee [校对的已付款金额,单位是分]
     * @return [bool]             [description]
     */
    public function orderQuery($order_id='',$total_fee=false)
    {
        if (!$order_id) return false; //如果订单号为空，则返回false
        if (!$total_fee) return false; //如果支付金额为空，则返回false
        $url = "https://api.mch.weixin.qq.com/pay/orderquery"; //微信那边的查询订单接口
        $data['appid'] = $this->appid;
        $data['mch_id'] = $this->mch_id;
        $data['out_trade_no'] = $order_id;
        $data['nonce_str'] = $this->getNonceStr(); //随机字符串32位
        $data['sign'] = $this->MakeSign($data); //生成签名
        $xml = $this->ToXml($data); //生成XML格式
        if ($xml === false) return '非法XML！';
        $xmlData = $this->postXmlCurl($url,$xml); //发起curl请求
        $result = $this->FromXml($xmlData);//xml转化为数组形式
        if ( @$result['total_fee'] == $total_fee && @$result['trade_state'] == 'SUCCESS' && @$result['trade_state_desc'] == '支付成功' ) {
            return true;
        }
        return false;
    }

    /**
     * [orderQuery 小程序订单退款]
     * @param  string  $transaction_id  [微信平台的订单号，支付成功回调返回]
     * @param  string $total_fee [订单总金额，单位为分]
     * @param  string $refund_fee [需要退款的金额，单位为分]
     * @param  string $notify_url [回调地址]
     * @return [bool]             [description]
     */
    public function orderRefund($transaction_id,$total_fee,$refund_fee,$notify_url=false)
    {
        if (!$transaction_id) {
            return [
                'status' => false,
                'msg' => '订单号为空！',
            ];
        }
        if (!$total_fee) {
            return [
                'status' => false,
                'msg' => '退款订单总金额为空！',
            ];
        }
        if (!$refund_fee) {
            return [
                'status' => false,
                'msg' => '退款金额为空！',
            ];
        }
        $total_fee = substr( sprintf("%.3f",$total_fee),0,-1 ); //保留两位小数 不进行四舍五入处理
        $refund_fee = substr( sprintf("%.3f",$refund_fee),0,-1 ); //保留两位小数 不进行四舍五入处理

        $url = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
        $data['appid'] = $this->appid;
        $data['mch_id'] = $this->mch_id;
        $data['transaction_id'] = $transaction_id;
        $data['out_refund_no'] = $transaction_id;
        $data['nonce_str'] = $this->getNonceStr(); //随机字符串32位
        $data['total_fee'] = $total_fee*100; //单位为分
        $data['refund_fee'] = $refund_fee*100; //单位为分

        if ($notify_url) {
            $data['notify_url'] = $notify_url;
        }

        $data['sign'] = $this->MakeSign($data); //生成签名
        $xml = @$this->ToXml($data); //生成XML格式
        if ($xml === false) {
            return [
                'status' => false,
                'msg' => '非法XML！',
            ];
        }
        $xmlData = $this->postXmlCurl($url,$xml,30,true); //发起curl请求
        $result = $this->FromXml($xmlData);//xml转化为数组形式
        return $result;
        //dump($result);
        //exit;
    }

}