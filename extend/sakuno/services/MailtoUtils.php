<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/8/10 19:47
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

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * 邮件工具(composer需安装PHPMailer)
 * Class MailtoUtils
 * @package sakuno\services
 */
class MailtoUtils
{

    /**
     * @param $to 收件用户
     * @param $title 邮件标题
     * @param $content 邮件内容
     * @param array $config 配置信息
     * @param array $other 其他
     * @return bool|string
     */
    static public function mailto($to,$title,$content,$config = [],$other = []){
        // 校验config
        if(empty($config)){
            return '请检查您的邮件配置信息';
        }
        // 判断发送和接收
        if($to == $config['Username'])
            return '发送邮箱和接收邮箱不能一致！';
        // send mail and get error
        try{
            $mail = new PHPMailer();
            //Server settings
            $mail->SMTPDebug = isset($config['SMTPDebug']) ? $config['SMTPDebug'] : false;      // 是否debug
            $mail->isSMTP();
            $mail->Host = $config['SMTPHost'];            // qq邮箱的服务器地址
            $mail->SMTPAuth = isset($config['SMTPAuth']) ? $config['SMTPAuth'] : true;         // 是否授权
            $mail->Username = $config['Username'];        // 授权的qq邮箱
            $mail->Password = $config['Password'];        // qq授权码，不是密码！！！
            $mail->CharSet = isset($config['CharSet']) ? $config['CharSet'] : 'utf-8';         // 字符编码
            $mail->SMTPSecure = isset($config['SMTPSecure']) ? $config['SMTPSecure'] : 'ssl';  // 使用 ssl 加密方式登录
            $mail->Port = $config['Port'];                // smtp 服务器的远程服务器端口号
            //Recipients
            $mail->setFrom($config['Username'], $config['setNickname']);  // 授权的qq邮箱（和上面一样），自己起的昵称
            $mail->addAddress($to);                                  // 传过来的收件人
            $mail->isHTML(isset($config['isHTML']) ? $config['isHTML'] : true);        // Set email format to HTML
            $mail->Subject = $title;                                 // 传过来的标题
            $mail->Body = $content;                                  // 传过来的内容
            // other
            if(!empty($other)){
                // 判断是否有附件
                if(isset($other['attachment'])){
                    foreach($other['attachment'] as $k => $v){
                        $mail->AddAttachment($v);   // 绝对路径
                    }
                }
            }
            return $mail->send();
        }catch (Exception $e){
            return $mail->ErrorInfo;
        }
    }

}
