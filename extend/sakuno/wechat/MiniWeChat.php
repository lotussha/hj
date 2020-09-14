<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/8/10 19:49
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

use EasyWeChat\Factory;

/**
 * 微信小程序 - 基于easywechat
 * composer require overtrue/wechat:~4.0 -vvv
 * Class MiniWeChat
 * @package sakuno\wechat
 */
class MiniWeChat
{
    /**
     * 创建小程序静态私有的变量对象
     * @var $instance
     */
    static private $instance;

    /**
     * [$config 小程序app应用]
     * @var array
     */
    private $app = null;

    /**
     * __construct 实例化
     * MiniWeChat constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->app = Factory::miniProgram($this->config);
    }

    /**
     * [__clone 防止克隆对象]
     */
    private function __clone(){

    }

    /**
     * getInstance 单列化
     * @param $config
     * @return MiniWechat
     */
    static public function getInstance($config){
        if (!self::$instance instanceof self) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * getApp 获取对象
     * @return array|\EasyWeChat\MiniProgram\Application
     */
    public function getApp(){
        return $this->app;
    }

    /**
     * getCode 微信小程序登陆
     * @param $code code参数
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function getCode($code){
        $auth = $this->app->auth->session($code);
        return $auth;
    }

    /**
     * 获取数量较少的业务场景小程序码
     * @param $path  必选参数:小程序页面路径
     * @param array $optional  可选参数数组
     *                          	width Int - 默认 430 二维码的宽度
     *								auto_color 默认 false 自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调
     *								line_color 数组，auto_color 为 false 时生效，使用 rgb 设置颜色 例如 ，示例：["r" => 0,"g" => 0,"b" => 0]。]
     * @param $to_path  保存的目标路径
     * @param $to_name  保存的图片名称,带后缀
     * @return mixed
     */
    public function getAppCode($path, $optional = [], $to_path, $to_name){
        if(empty($optional)){
            $response = $this->app->app_code->get($path);
        }else{
            $response = $this->app->app_code->get($path,$optional);
        }
        return $this->saveImage($response,$to_path,$to_name);
    }

    /**
     * 适用于需要的小程序码数量极多，或仅临时使用的业务场景
     * @param $scene  必选参数
     * @param array $optional  可选参数数组
     *                          	page string 小程序页面路径
     *                          	width Int - 默认 430 二维码的宽度
     *								auto_color 默认 false 自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调
     *								line_color 数组，auto_color 为 false 时生效，使用 rgb 设置颜色 例如 ，示例：["r" => 0,"g" => 0,"b" => 0]。
     * @param $to_path  保存的目标路径
     * @param $to_name  保存的图片名称,带后缀
     * @return mixed
     */
    public function getUnlimit($scene, $optional = [], $to_path, $to_name){
        if(empty($optional)){
            $response = $this->app->app_code->getUnlimit($scene);
        }else{
            $response = $this->app->app_code->getUnlimit($scene,$optional);
        }
        return $this->saveImage($response,$to_path,$to_name);
    }

    /**
     * saveImage 保存图片
     * @param $response 小程序码获取对象
     * @param string $to_path 保存的目标路径
     * @param string $to_name 保存的图片名称,带后缀
     * @return bool|int 文件路径
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     */
    protected function saveImage($response,$to_path, $to_name){
        //保存小程序码到文件
        if ($response instanceof StreamResponse) {
            if($to_name){
                $filename = $response->save($to_path,$to_name);
            }else{
                $filename = $response->save($to_path);
            }
            return $filename;
        }
    }

    /**
     * 模板消息发送
     * @param array $optional
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendTemplate(array $optional){
        return $this->app->template_message->send($optional);
    }

    /**
     * encryptor 微信加密数据的解密方法
     * @param string $session
     * @param string $iv
     * @param string $encryptedData
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\DecryptException
     */
    public function encryptor($session,$iv,$encryptedData){
        return $this->app->encryptor->decryptData($session, $iv, $encryptedData);
    }

    /**
     * 用于校验一段文本是否含有违法内容]{单个appid调用上限为2000次/分钟，1,000,000次/天}
     * @param $content  检测内容
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function checkText($content){
        return $this->app->content_security->checkText($content);
    }

    /**
     * 小程序概况趋势 详情请看https://www.easywechat.com/docs/4.1/mini-program/data_cube
     * @param string $type  概况趋势:summaryTrend,访问日趋势:dailyVisitTrend,访问周趋势:weeklyVisitTrend,访问月趋势:monthlyVisitTrend,....
     * @param string $from  开始日期
     * @param string $to  结束日期
     * @return mixed
     */
    public function dataCube($type, $from, $to){
        return $this->app->data_cube->$type($from,$to);
    }

}
