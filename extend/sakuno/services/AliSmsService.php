<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/8/10 19:34
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
namespace sakuno\services;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use sakuno\utils\JsonUtils;

/**
 * 阿里云短信
 * Class AliSmsService
 * @package sakuno\services
 */
class AliSmsService
{

    /**
     * 发送国内短信
     * @param array $param 参数 eg: $param = [
            'accessKeyId'       => $system_sms['access_key_id'],
            'accessSecret'      => $system_sms['access_secret'],
            'regionId'          => $system_sms['region_id'],
            'phoneNumbers'      => $tel_phone,
            'signName'          => $system_sms['sign_name'],
            'templateCode'      => $system_sms['template_code'],
            'templateParam'     => ['code'=>$identify_code],
        ];
     * @param string $method 请求方法 ,默认post
     * @param string $scheme 请求协议 默认http
     * @param string $action 调用方法 默认 SendSms
     * @param string $version 版本
     * @return \AlibabaCloud\Client\Result\Result|array|\think\Response
     * @throws ClientException
     */
    static public function sendAliSmsChina($param = [],$method = 'POST',$scheme = 'http',$action = 'SendSms',$version = '2017-05-25'){
        // 初始化配置
        AlibabaCloud::accessKeyClient($param['accessKeyId'],$param['accessSecret'])
            ->regionId($param['regionId'])
            ->asDefaultClient();
        // http请求 并且捕获异常
        try{
            // 发起请求
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                ->scheme($scheme)
                ->version($version)
                ->action($action)
                ->method($method)
                ->host('dysmsapi.aliyuncs.com')
                ->options(['query'=>[
                    'RegionId' => $param['regionId'],
                    'PhoneNumbers' => $param['phoneNumbers'], // 手机号
                    'SignName' => $param['signName'], // 配置签名
                    'TemplateCode' => $param['templateCode'], // 配置短信模板编号
                    'TemplateParam' => json_encode($param['templateParam']) // 短信模板变量替换JSON串,友情提示:如果JSON中需要带换行符,请参照标准的JSON协议
                ]])
                ->request();
        }catch (ClientException $e){
            return JsonUtils::returnDataErr($e->getErrorMessage(),INTERFACE_OUTTER_INVOKE_ERROR);
        }catch (ServerException $e){
            return JsonUtils::returnDataErr($e->getErrorMessage(),INTERFACE_OUTTER_INVOKE_ERROR);
        }
        if(!empty($result)){
            $result = $result->toArray();
        }
        return JsonUtils::returnDataSuc('ok',SUCCESS,$result);
    }


}
