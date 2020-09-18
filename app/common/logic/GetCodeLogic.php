<?php


namespace app\common\logic;

//获取验证码
use app\common\model\config\CodeGainModel;
use app\common\model\config\ShortMessageInterfaceConfigModel;
use app\common\model\config\ShortMessageModelConfigModel;
use sakuno\utils\JsonUtils;
use think\facade\Db;

class GetCodeLogic
{
    /**
     * 验证码
     * User: hao  2020.09.08
     */
    public function get_code($receive)
    {
        $type = $receive['type'] ?? '';
        $phone = $receive['phone'] ?? '';
        $scenes_id = $receive['scenes_id'] ?? '';

        if (!preg_match("/^1[34578]{1}\d{9}$/", $phone)) {
            return JsonUtils::fail('手机号有误', PARAM_IS_INVALID);
        }

        //请求类型，缓存的键值都不一样  1:小程序  2:后台  3:app
        if ($type == 1) {
//            $key = 'cache_applets_code:' . $phone;
        } else if ($type == 2) {
//            $key = 'cache_back_code:' . $phone;
        } else if ($type == 3) {
//            $key = 'cache_app_code:' . $phone;
        } else {
            return JsonUtils::fail('参数有误', PARAM_IS_INVALID);
        }

        $code = getRandomStr(); //生成随机验证码

        //获取模型
        $content = (new ShortMessageModelConfigModel())->findInfo(['scene_id' => $scenes_id, 'status' => 1], 'autograph,message_content');
        $content_str = $content['autograph'] . $content['message_content'];
        $content_str = str_replace('${code}', $code, $content_str);

        $ip = request()->ip(); //获取访问者的ip地址

        //手机号发送次数（一小时）
        $time = time();
        $one_time = time() - 60 * 60 * 1;
        $where_p_send_num = array();
        $where_p_send_num[] = ['phone', '=', $phone];
        $where_p_send_num[] = ['start_time', '>', $one_time];
        $where_p_send_num[] = ['start_time', '<', $time];
        $phone_send_num = (new CodeGainModel())->where($where_p_send_num)->count();
        if ($phone_send_num > 20) {
            return JsonUtils::fail('一小时内禁止超发20条短信！');
        }

        //获取ip
        $ip = request()->ip();
        $day = strtotime(date('Y-m-d'));
        $where_i_send_num = array();
        $where_i_send_num[] = ['ip', '=', $ip];
        $where_i_send_num[] = ['start_time', '>', $day];
        $where_i_send_num[] = ['start_time', '<', $time];
        $ip_send_num = (new CodeGainModel())->where($where_i_send_num)->count();
        if ($ip_send_num > 360) {
            return JsonUtils::fail('IP封禁，盗刷警告！');
        }

        //获取手机最后的时间
        $last_time = (new CodeGainModel())->where(['phone'=>$phone])->order('start_time desc')->find();


        //保存数据
        Db::startTrans();
        $over_time = 300;//5分钟
        $data_code = array();
        $data_code['phone'] = $phone;
        $data_code['type'] = $type;
        $data_code['start_time'] = $time;
        $data_code['end_time'] = $time + $over_time;
        $data_code['code'] = $code;//验证码
        $data_code['ip'] = $ip;//ip
        $data_code['scenes'] = $scenes_id;//场景
        $res = (new CodeGainModel())->create($data_code);
        if (!$res) {
            Db::rollback();
            return JsonUtils::fail('发送失败');
        }

        $sendRes = $this->sendCodes($phone, $content_str); //发送短信
        dump($sendRes);exit;
        if ($sendRes['code'] === false) {
            Db::rollback();
//            return JsonUtils::fail('系统繁忙，发送短信异常，请联系管理员！');
            return JsonUtils::fail($sendRes['msg']);
        }
        Db::commit();
        $data['over_time'] = $over_time; //过期时间
        $data['resend_time'] = 60; //可重新发送时间
        return JsonUtils::successful('发送成功',$data);
    }

    //发送短信
    public function sendCodes($phone, $content)
    {
        $this->aliyunSms();
        $list = (new ShortMessageInterfaceConfigModel())->where(['stutas'=>1,'delete_time'=>0])->field('id,appkey,secretkey')->find();
        if (!$list){
            return ['code' => false, 'msg' => '没有发送短信接口'];
        }
        switch ($list['id']){
            case 1:

                break;
        }
        return ['code' => true, 'msg' => ''];
    }


    //阿里云
    public function aliyunSms(){
        echo 1;
    }
}