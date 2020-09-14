<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: jomlz
 *           '::::::::::::::..                                   Time: 2020/8/10 14:03
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
namespace app\common\logic\user;

use app\common\model\log\SyslogsModel;
use app\common\model\log\UserAccountLogModel;
use app\common\model\user\UserModel;
use sakuno\utils\JsonUtils;

/**
 * 用户账户日志
 * Class UserAccountLogLogic
 * @package app\common\logic\log
 */
class UserAccountLogLogic
{

    /**
     * 记录用户账户
     * @param int $user_id
     * @param int|null $type
     * @param array $data
     * @param string $remarks
     * @param bool $is_trans
     * @return array
     * User: Jomlz
     */
    // TODO 获取用户 佣金/资金/积分 情况(待完善,现虚拟)
    public static function userAccountLogAdd(int $user_id,int $type = null, array $data = [], string $remarks = '' ,int $pay_mode = 0, bool $is_trans = false)
    {
//        dump($data);die;
        // 判断参数
        if(empty($user_id) || !is_numeric($user_id) || empty($type)){
            $data = ['message'=>'参数异常','line'=>(__LINE__) - 2,'file'=>__FILE__,'level'=>3,'bus_explain'=>'记录用户账户'];
            (new SyslogsModel())->addLog($data);
            return JsonUtils::returnDataErr('参数异常',PARAM_IS_INVALID);
        }
        //校验用户是否存在
        $user_info = (new UserModel())->with(['UserGrade','UserDetails'])->find($user_id)->toArray();
//        dump($user_info);die;
        if (!$user_info){
            $data = ['message'=>'用户信息不存在','line'=>(__LINE__) - 2,'file'=>__FILE__,'level'=>3,'bus_explain'=>'记录用户账户'];
            (new SyslogsModel())->addLog($data);
            return JsonUtils::returnDataErr('用户信息不存在',USER_NOT_EXIST);
        };
        $account = [];
        //公共参数
        $save_data = ['user_id'=>$user_id,'type'=>$type,'remarks'=>$remarks,'create_time'=>time(),'pay_mode'=>$pay_mode,
            'original_money'=>$user_info['UserDetails']['use_money'],
            'original_frozen_money'=> '',
            'original_com_money'=> $user_info['UserDetails']['commission_use_money'],
            'original_com_frozen_money'=> $user_info['UserDetails']['commission_frozen_money'],
        ];
        // TODO  校验类型 1下单 2充值 3提现 4下单退款 5提现驳回 6佣金
        switch((int)$type){
            case 1:
                $save_data['change_money'] = $data['final_price'];
                $save_data['goods_id'] = $data['goods_id'];
                $save_data['order_id'] = $data['order_id'];
                $save_data['identity'] = $data['identity'];
                $save_data['identity_id'] = $data['identity_id'];
                if ($pay_mode == 3){
                    $save_data['current_money'] = $user_info['UserDetails']['use_money'] - $data['final_price'];
                }
                break;
            case 2:
                // 判断余额参数是否为空
                if(!isset($data['change_balance'])) return JsonUtils::returnDataErr('余额调整值不能为空',PARAM_IS_BLANK);
                break;
            case 3:
                // 判断积分参数是否为空
                if(!isset($data['change_integral'])) return JsonUtils::returnDataErr('积分调整值不能为空',PARAM_IS_BLANK);
                break;
            case 6:
                $save_data['change_money'] = $data['money'];
                $save_data['goods_id'] = $data['goods_id'];
                $save_data['order_id'] = $data['order_id'];
                break;
            default:
                $data = ['message'=>'记录类型错误','line'=>(__LINE__) - 2,'file'=>__FILE__,'level'=>3,'bus_explain'=>'记录用户账户'];
                (new SyslogsModel())->addLog($data);
                return JsonUtils::returnDataErr('记录类型错误',PARAM_TYPE_BIND_ERROR);
                break;
        }
        if (!(new UserAccountLogModel())->userAccountLogAdd($save_data)){
            $data = ['message'=>'添加失败','line'=>(__LINE__) - 1,'file'=>__FILE__,'level'=>3,'bus_explain'=>'记录用户账户'];
            (new SyslogsModel())->addLog($data);
            return JsonUtils::returnDataErr('添加失败',ERROR);
        }
        return JsonUtils::returnDataSuc('记录成功',SUCCESS);
    }


}
