<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/8/10 20:10
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
namespace sakuno\repositories;

/**
 * 支付储存库 - 用于抽象数据层
 * Class PaymentRepositories
 * @package sakuno\repositories
 */
class PaymentRepositories
{

    /**
     * TODO 订单支付成功之后
     * @param string|null $order_id
     * @return bool
     */
    public static function wechatProduct(string $order_id = null){
        // TODO 支付成功后的操作 已支付返回true 否则返回false
        try{
            // 判断数据库订单是否支付成功
            $is_pay = true;  // TODO 虚拟数据 正式使用请结合业务使用
            if($is_pay) return true;
            // TODO 支付成功所需要的操作 根据业务需求调用自己的model类方法 比如更新订单状态等等 然后根据成功与否 返回 true or false
            return true;
        }catch (\Exception $e){
            return false;
        }
    }

    // TODO 如果需要其他支付回调监听 可以按照以上方法构造 wechat为支付回调HOOK时传入固定参数 product为支付时传入的attach参数

}
