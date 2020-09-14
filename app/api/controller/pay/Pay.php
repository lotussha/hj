<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/24
 * Time: 14:56
 */

namespace app\api\controller\pay;

use app\api\controller\Api;
use app\api\logic\pay\PayLogic;
use app\api\validate\PayValidate;
use app\common\model\PayLogModel;
use app\Request;
use jomlz\Wxpay\WxPayApi;
use sakuno\utils\JsonUtils;
use think\App;

class Pay extends Api
{
    protected $validate;
    protected $payLogic;
    public function __construct(Request $request, App $app ,PayValidate $validate,PayLogic $payLogic)
    {
        $this->validate = $validate;
        $this->payLogic = $payLogic;
        parent::__construct($request, $app);
    }

    /**
     * 获取支付参数
     * User: Jomlz
     */
    public function pay_parameter()
    {
        $validate_result = $this->validate->check($this->param);
        if (!$validate_result) {
            return JsonUtils::fail($this->validate->getError());
        }
//        $WxPayApi = new WxPayApi();
//        dump($WxPayApi);die;
        //添加openid --hao
        $this->param['openid'] = $this->api_user['openid'];
        $res = $this->payLogic->getPayParameter($this->param);
        dump($res);die;
        if ($res['status'] == 1){
            return JsonUtils::successful('成功',$res['data']);
        }else{
            return JsonUtils::fail($res['msg']);
        }
    }

    /**
     * 检查支付状态
     * User: Jomlz
     */
    public function check_pay_status()
    {
        $log_id = $this->param['log_id'] ?? '';
        if (empty($log_id)){
            return JsonUtils::fail('违法参数');
        }
        $log_info = (new PayLogModel())->where(['user_id'=>$this->user_id])->find();
        if (!$log_info){
            return JsonUtils::fail('违法参数');
        }
        if ($log_info['is_pay'] == 1){
            return JsonUtils::successful('支付成功');
        }else{
            return JsonUtils::successful('支付失败');
        }
    }

}