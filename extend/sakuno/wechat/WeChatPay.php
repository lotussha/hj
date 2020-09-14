<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/8/10 19:53
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
namespace sakuno\wechat;

use sakuno\repositories\PaymentRepositories;
use sakuno\utils\Hook;
use EasyWeChat\Factory;

/**
 * 微信支付 - 基于easywechat
 * composer require overtrue/wechat:~4.0 -vvv
 * Class WeChatPay
 * @package sakuno\wechat
 */
class WeChatPay
{

    /**
     * 创建微信静态私有的变量对象
     * @var $instance
     */
    static private $instance;

    /**
     * [$config 微信支付配置参数]
     * @var array
     */
    private $config = [];

    /**
     * [$config 微信支付app应用]
     * @var array
     */
    private $payment = null;

    /**
     * __construct 实例化
     * WeChatPay constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = $this->getConfig($config);
        //$this->config = $config;
        $this->payment = Factory::payment($this->config);
    }

    /**
     * [__clone 防止克隆对象]
     */
    private function __clone(){

    }

    /**
     * getInstance 单列化
     * @param $config
     * @return WechatPay
     */
    static public function getInstance($config){
        if (!self::$instance instanceof self) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * 处理config
     * @param array $config
     * @return array
     */
    protected function getConfig($config=[])
    {
        // 日志等级 默认debug
        $level = 'debug';
        $wechat_config = [
//            'app_id'    =>$site['wechat_appid'],  // appId
//            'secret'    =>$site['wechat_secret'], // secret
//            'mch_id'    =>$site['wechat_mch_id'], // 商户号
//            'key'       =>$site['wechat_key'],    // API密钥
//            'cert_path' =>APP_PATH.$site['wechat_cert_path'], // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
//            'key_path'  =>APP_PATH.$site['wechat_key_path'], // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
//            'notify_url'  => request()->domain().'/api/v1/notify', // 支付回调
            'log' => [
                'level' => $level,
                'file' => '../runtime/WeChatPay/'.$level.'/'.date("Ym").'/WeChatPay_'.date('d').'.log',
            ]
        ];
        if($config){
            return array_merge($wechat_config,$config);
        }else{
            return $wechat_config;
        }
    }

    /**
     * getPayment 获取对象
     * @return array|\EasyWeChat\MiniProgram\Application
     */
    public function getPayment(){
        return $this->payment;
    }

    /**
     * 唯一支付
     * @param $title 商品信息
     * @param $out_trade_no 订单号
     * @param $total_fee 订单金额
     * @param string $openid 用户openid
     * @param string $attach 不同支付场景 比如商品传入Product 与PaymentRepositories方法相对应 重要!!!!
     * @param string $trade_type
     * @param string $product_id
     * @param bool $isContract
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function payment($title,$out_trade_no,$total_fee,$openid = '',string $attach,$trade_type = 'JSAPI', $product_id = '',$isContract = false) {
        $result = $this->payment->order->unify([
            'body' => $title,
            'out_trade_no' => $out_trade_no,
            'total_fee' => $total_fee * 100,
            'trade_type' => $trade_type, // 请对应换成你的支付方式对应的值类型
            'openid' => $openid,
            'product_id' => $product_id,
            'attach' => $attach
        ], $isContract);
        return $result;
    }

    /**
     * 生成支付 JS 配置
     * @param $prepay_id
     * @param bool $is_json 返回 json 字符串，如果想返回数组，传第二个参数 false
     * @return mixed
     */
    public function bridgeConfig($prepay_id,$is_json = false){
        return $this->payment->jssdk->bridgeConfig($prepay_id,$is_json);
    }

    /**
     * 微信退款
     * @param $number 商户订单号
     * @param $refundNumber 商户退款单号
     * @param $totalFee 订单金额
     * @param $refundFee 退款金额
     * @param string $refund_desc 其他参数 退款原因
     * @param string $notify_url 其他参数 退款回调
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function refund($number,$refundNumber,$totalFee,$refundFee,$refund_desc = '退款',$notify_url = ''){
        // 参数分别为：商户订单号、商户退款单号、订单金额、退款金额、其他参数
        $result = $this->payment->refund->byOutTradeNumber($number, $refundNumber,$totalFee*100,$refundFee*100, [
            'refund_desc' => $refund_desc,
            'notify_url' => $notify_url
        ]);
        return $result;
    }

    /**
     * 微信扫码支付
     * @param $productId
     * @return string
     */
    public function scheme($productId){
        $result = $this->payment->scheme($productId);
        return $result;
    }

    /**
     * 微信支付成功回调接口
     */
    public function notify(){
        $response = $this->payment->handlePaidNotify(function($notify, $successful) {
            if ($successful && isset($notify->out_trade_no)) {
                if (isset($notify->attach) && $notify->attach) {
                    if (($count = strpos($notify->out_trade_no, '_')) !== false) {
                        $notify->out_trade_no = substr($notify->out_trade_no, $count + 1);
                    }
                    // TODO 行为事件 具体不同场景支付成功后操作 可以在PaymentRepositories自行添加操作方法 命名规则为 wechat拼接attach参数值 如 wechatProduct
                    return (new Hook(PaymentRepositories::class, 'wechat'))->listen($notify->attach, $notify->out_trade_no);
                }
                // TODO 下面可进行支付记录 暂时屏蔽不需要使用 根据业务需要可以自定义方法后开启
                // WechatMessage::setOnceMessage($notify, $notify->openid, 'payment_success', $notify->out_trade_no);
                return false;
            }
        });
    }

}
