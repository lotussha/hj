<?php


namespace app\api\logic;


//用户登录
use app\common\model\config\WebsiteConfigModel;
use app\common\model\user\UserDetailsModel;
use app\common\model\user\UserGradeModel;
use app\common\model\user\UserModel;
use app\common\model\user\UserUpgradeLogModel;
use app\common\model\OperateRedis;
use sakuno\utils\JsonUtils;
use WeChatApplets\UserInfoDecryption\wxBizDataCrypt;
use think\facade\Cache;

class LoginHandleLogic
{
    //应用实例
    protected $appid;  //微信小程序的唯一id

    protected $appSecret; //微信小程序的密钥

    public function __construct()
    {
        $website = new WebsiteConfigModel();
        $website_list = $website->where('type', 'in', 'xiaoappid,app_secret')->field('type,val')->column('val', 'type');
        $this->appid = $website_list['xiaoappid'];
        $this->appSecret = $website_list['app_secret'];
    }

    /**
     * 用户授权+手机号
     * User: hao
     */
    public function login_phone($receive)
    {
        $User = new UserModel();
        $UserDetail = new UserDetailsModel();
        //获取code的信息
        $authInfo = $this->getCodeInfo($receive['wx_code']); //获取微信那边的登录session信息
        if (isset($authInfo['errcode'])) {
            return JsonUtils::fail($authInfo['errmsg']);
        }

//        dump($authInfo);exit();
        /*
         * ^ array:2 [
          "session_key" => "81Ev9KAu7VUCm2872izDVw=="
          "openid" => "otkhA5ceWxUnPE50P_jLof_6sSu8"
        ]
         * */
//        dump($authInfo);exit();
        //微信解密数据
        $userData = $this->wxDecryptData($authInfo['session_key'], $receive['encryptedData'], $receive['iv']); //解密用户基本信息或是手机号
        if ($userData['code']===false){
            return JsonUtils::fail($userData['msg']);
        }

        //整理数据
        $data = $this->handleParame($userData['data']);

        //用户名称转义
        if (!empty($data['nick_name'])) {
            $data['nick_name'] = EmojiEncode($data['nick_name']); // EmojiDecode
        }



        //如果手机密文不为空，则进行解密
        if (isset($receive['phone_encryptedData']) && isset($receive['phone_iv'])) {
            $phoneData = $this->wxDecryptData($authInfo['session_key'], $receive['phone_encryptedData'], $receive['phone_iv'], 'phone'); //解密用户基本信息或是手机号
            if ($phoneData['code']===false){
                return JsonUtils::fail($userData['msg']);
            }
            $data_user = array();
            $data_user['phone'] = $phoneData['data']['purePhoneNumber']; //获取用户手机号
            $userInfo = $User->findInfo(['phone' => $data_user['phone']], 'id,share_id,username,grade_id,nick_name,phone,avatar_url,status,openid');
//            dump($data_user['phone']);exit();
//            dump($userInfo);exit();
            //已存在用户
            if ($userInfo) {
                // 如果手机号存在，并且openid为空，那就查询一下，此微信有没有用其他手机号注册过小程序
                if (@!$userInfo['openid']) {
                    $userInfo1 = $User->field('id,phone,openid')->where(['openid' => $data['openid'],])->find(); //查询微信号是否已注册过

                    if (!empty($userInfo1['id'])) {
                        return JsonUtils::fail('您这个微信号已注册过了，请用之前的注册手机号:' . $userInfo1['phone'] . '进行登录！');
                    }
                } else {

                    if ($userInfo['openid'] != $data['openid']) {
                        $userInfo1 = $User->field('id,phone,openid')->where(['openid' => $data['openid'],])->find(); //查询微信号是否已注册过
                        if (!empty($userInfo1['id'])) {
                            return JsonUtils::fail('您这个微信号已注册过了，请用之前的注册手机号:' . $userInfo1['phone'] . '进行登录！');
                        }
                    }
                }

                $data_user['nick_name'] = $data['nick_name'];  //昵称
                $data_user['avatar_url'] = $data['avatar_url'];//用户头像
                $data_user['last_time'] = $data['last_time'];//最近一次登录时间
                $res = $User->updateInfo(['phone' => $data_user['phone']], $data_user);//更新最新数据
                //返回数据
                return $this->handleUserLoginCache($userInfo['id']);
            } else {
                //没有注册
                $userInfo2 = $User->findInfo(['openid' => $data['openid']], 'id,phone,openid');

                if (!empty($userInfo2['id'])) {
                    return JsonUtils::fail('您这个微信号已注册过了，请用之前的注册手机号:' . $userInfo2['phone'] . '进行登录！');
                }

                //走这一步是新注册用户
                //后面才加的需求，没有分享人就默认为指定用户
                $share_id ='';
                if  (empty($receive['share_id']) || !isset($receive['share_id'])){
                    $share_id = 38;
                }
                //如果有分享id,则进行id记录
                if ($share_id) {
                    $data_user['share_id'] = $share_id; //直推id
                    //查看上级是否够是升级
                    $this->share_num($share_id);
                }
                $data_user['nick_name'] = $data['nick_name'];  //昵称
                $data_user['avatar_url'] = $data['avatar_url'];//用户头像
                $data_user['openid'] = $data['openid']; //当前小程序的唯一id
                $data_user['last_time'] = $data['last_time'];//最近一次登录时间

                $uid = $User->addInfoId($data_user); //插入用户数据

                if (!$uid) {
                    $User->rollbackTrans(); //事务回滚
                    return JsonUtils::fail('注册失败');
                }
                $data_detail = array();
                $data_detail['uid'] = $uid;  //用户id
                $data_detail['sex'] = $data['sex']; //性别 0：未知、1：男、2：女
                $data_detail['country'] = $data['country']; //所属国家
                $data_detail['province'] = $data['province'];  //所在省
                $data_detail['city'] = $data['city']; //所在市

                $res = $UserDetail->insert($data_detail);
                if (!$res) {
                    $User->rollback(); //事务回滚
                    return JsonUtils::fail('注册失败');
                }
//                $userInfo = $User->findInfo(['id' => $uid], 'id,share_id,username,grade_id,nick_name,phone,avatar_url,status,openid');

                //返回数据
                return $this->handleUserLoginCache($uid);
            }
        }

        //走这一步是老用户登录操作
//        $userInfo = $User->findInfo(['openid' => $data['openid']], 'id,share_id,username,grade_id,nick_name,phone,avatar_url,status,openid');  //查询是否已注册
        $userInfo = $User->findInfo(['openid' => $data['openid']], 'id');  //查询是否已注册

        if (!$userInfo) {
            return JsonUtils::fail('新用户的手机数据不能为空');
        }
        $data_user = array();
        $data_user['nick_name'] = $data['nick_name'];  //昵称
        $data_user['avatar_url'] = $data['avatar_url'];//用户头像
        $data_user['last_time'] = $data['last_time'];//最近一次登录时间
        $res = $User->updateInfo(['openid' => $data['openid']], $data_user);
        //返回数据
        return $this->handleUserLoginCache($userInfo['id']);

    }


    /**
     * 用户登录 + 绑定手机号（后面接口）
     * User: hao
     */
    public function Login($receive)
    {

        $UserModel = new UserModel();

        //获取code的信息
        $authInfo = $this->getCodeInfo($receive['wx_code']); //获取微信那边的登录session信息
        if (isset($authInfo['errcode'])) {
            return JsonUtils::fail($authInfo['errmsg']);
        }

        $openid = $authInfo['openid'];

        $user = $UserModel->findInfo(['openid' => $openid]);

        $data_user = array();
        $data_user['openid'] = $openid;

        //用户名称转义
        if (!empty($receive['nick_name'])) {
            $data_user['nick_name'] = EmojiEncode($receive['nick_name']); // EmojiDecode
        }
        //用户头像
        $data_user['avatar_url'] = $receive['avatar_url'];
        $data_user['last_time'] = time(); //最后登录

        //存在登录
        if ($user && $user['username']) {
            if ($user['status'] == 2) {
                return JsonUtils::fail('用户已给禁用', USER_ACCOUNT_FORBIDDEN);
            }
            $res = $UserModel->updateInfo(['openid' => $data_user['openid']], $data_user);//更新最新数据

            //返回数据
            return $this->handleUserLoginCache($user['id']);
        } else {
            //跳去绑定
            return JsonUtils::successful('获取成功跳去绑定', ['openid' => $openid], USER_NOT_BINGING);

        }

    }


    /**
     * 用户绑定
     * User: hao
     */
    public function bind($receive)
    {

        $UserModel = new UserModel();
        $UserDetail = new UserDetailsModel();
//        $OperateRedis = new OperateRedis(); //封装的redis模型
//        $cache_app_code = $OperateRedis->get('cache_app_code:' . $receive['username']); //获取手机验证码

        //验证码
//        if ($receive['code'] != $cache_app_code) {
//            return JsonUtils::fail('验证码已失效');
//        }
        $data_user = array();
        $data_user['username'] = $receive['username'];
        $data_user['openid'] = $receive['openid'];
        $data_user['avatar_url'] = $receive['avatar_url'];
        $data_user['nick_name'] = $receive['nick_name'];
        $data_user['phone'] = $receive['phone'] ?? '';
        $data_user['share_id'] = $receive['share_id'] ?? '38';
        $data_user['last_time'] = time();


        //判断该用户是否已绑定手机号
        $username = $UserModel->getValues(['username'=>$receive['username']],'username');
        if ($username){
            return JsonUtils::fail('该手机号已绑定过，请更换手机号');
        }


        //密码加密
        $data_user['password'] = password_hash($receive['password'], 1);
        $UserModel->beginTrans();



        $uid = $UserModel->addInfoId($data_user); //插入用户数据

        if (!$uid) {
            $UserModel->rollbackTrans(); //事务回滚
            return JsonUtils::fail('绑定失败');
        }
        $data_detail = array();
        $data_detail['uid'] = $uid;  //用户id
        $data_detail['sex'] = $receive['sex'] ?? '0'; //性别 0：未知、1：男、2：女
        $data_detail['country'] = $receive['country'] ?? ''; //所属国家
        $data_detail['province'] = $receive['province'] ?? '';  //所在省
        $data_detail['city'] = $receive['city'] ?? ''; //所在市

        $res = $UserDetail->insert($data_detail);
        if (!$res) {
            $UserModel->rollback(); //事务回滚
            return JsonUtils::fail('绑定失败');
        }
        if ($data_user['share_id']){
            //查看上级是否够是升级
            $this->share_num($data_user['share_id']);
        }

        $UserModel->commitTrans(); //事务提交
        //返回数据
        return $this->handleUserLoginCache($uid);
    }


    /**
     * 用户获取验证码
     * User: hao
     */
    public function code($receive)
    {
        $type = $receive['code_type'];
        $phone = $receive['phone'];
        //请求类型，缓存的键值都不一样  1:小程序  2:后台  3:app
        if ($type == 1) {
            $key = 'cache_applets_code:' . $phone;
        } else if ($type == 2) {
            $key = 'cache_back_code:' . $phone;
        } else if ($type == 3) {
            $key = 'cache_app_code:' . $phone;
        } else {
            return JsonUtils::fail('参数有误', PARAM_IS_INVALID);
        }

        $code = getRandomStr(); //生成随机验证码
        $content = '您的验证码是：' . $code . '，有效期为2分钟，请勿告诉他人，如非本人操作请忽略！';

        $ip = request()->ip(); //获取访问者的ip地址
        $OperateRedis = new OperateRedis(); //自行封装的redis操作类
        $phone_send_num = $OperateRedis->get('phone_send_num:' . $phone); //验证码发送次数
        $ip_send_num = $OperateRedis->get('ip_send_num:' . $ip); //ip发送次数

        //如果一小时内发过短信，则会记录手机号的发送次数，超过则会禁止，这个操作是防止非法盗刷短信
        if (isset($phone_send_num)) {
            if ($phone_send_num > 20) {
                return JsonUtils::fail('一小时内禁止超发20条短信！' . @$OperateRedis->ttl('phone_send_num:' . $phone) . '秒后重试！');
            }
        } else {
            $phone_send_num = 0;
        }

        //如果一天内发过短信，则会记录ip地址的发送次数，超过则会禁止，这个操作是防止非法盗刷短信
        if (isset($ip_send_num)) {
            if ($ip_send_num > 360) {
                return JsonUtils::fail('IP封禁，盗刷警告！');
            }
        } else {
            $ip_send_num = 0;
        }

        $sendRes = sendCodes($phone, $content); //发送短信
        if ($sendRes) {
            $redisSet = $OperateRedis->set($key, $code, 120); //缓存120秒
            if ($phone_send_num) {
                $phone_over_time = $OperateRedis->ttl('phone_send_num:' . $phone);
                $phone_over_time = !$phone_over_time || $phone_over_time <= 0 ? 3600 : $phone_over_time;
                @$OperateRedis->set('phone_send_num:' . $phone, $phone_send_num + 1, $phone_over_time); //验证码发送次数
            } else {
                @$OperateRedis->set('phone_send_num:' . $phone, $phone_send_num + 1, 3600); //验证码发送次数
            }

            if ($ip_send_num) {
                $ip_over_time = $OperateRedis->ttl('ip_send_num:' . $ip); //获取已缓存的时间
                $ip_over_time = !$ip_over_time || $ip_over_time <= 0 ? 86400 : $ip_over_time;
                @$OperateRedis->set('ip_send_num:' . $ip, $ip_send_num + 1, $ip_over_time); //ip发送次数
            } else {
                @$OperateRedis->set('ip_send_num:' . $ip, $ip_send_num + 1, 86400); //ip发送次数
            }

            $data['over_time'] = 120; //过期时间
            $data['resend_time'] = 60; //可重新发送时间
            if ($redisSet) {
                return JsonUtils::successful('操作成功', $data);
            }
        }
        return JsonUtils::fail('系统繁忙，发送短信异常，请联系管理员！');
    }


    /**
     * 获取code的信息
     * User: hao
     */
    private function getCodeInfo($code)
    {
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$this->appid}&secret={$this->appSecret}&js_code={$code}&grant_type=authorization_code";

        $authInfo = curlHttp($url, 'GET'); //请求微信接口，code换取openId和session_key
        return $authInfo;
    }


    /**
     * 微信解密数据
     * User: hao
     */
    public function wxDecryptData($session_key, $encryptedData, $iv, $type = 'user')
    {

        $wxDc = new WXBizDataCrypt($this->appid, $session_key);

        $data = $wxDc->decryptData($encryptedData, $iv);
        if ($data['code'] === 0){
            return ['code'=>true,'data'=>json_decode($data['data'], TRUE)];
//            return json_decode($data['data'], TRUE);
        }
        return ['code'=>false,'msg'=>$data['code'] . ' ---' . $type];
//        dump($data);exit();
//        return $data;
//        if ($data['code'] === 0) {
//            return json_decode($data['data'], TRUE);
//        }
//        jsonReturn(403, $data['code'] . ' ---' . $type);
    }

    /**
     * [handleParame description]
     * @param  [type] $userData [一维数组]
     * @return [type] $data     [整合好的一维数组]
     * User: hao  2020.08.21
     */
    public function handleParame($userData)
    {
        $data['nick_name'] = $userData['nickName']; //用户昵称
        $data['avatar_url'] = $userData['avatarUrl']; //用户头像
        $data['openid'] = $userData['openId']; //当前小程序的唯一id
        $data['sex'] = $userData['gender']; //性别 0：未知、1：男、2：女
        $data['country'] = $userData['country']; //所属国家
        $data['province'] = $userData['province']; //所在省
        $data['city'] = $userData['city']; //所在市
        $data['last_time'] = time(); //最近一次登录时间
        if (@$userData['unionId']) $data['unionid'] = $userData['unionId']; //微信应用的唯一id
        return $data;
    }

    /**
     * [handleUserLoginCache 处理登录后的缓存存储]
     * @param  [array] $where [查询条件数组，一维]
     * @return [array]        [返回给前端的用户数据]
     * User: hao  2020.08.21
     */
    public function handleUserLoginCache($uid)
    {

        $UserModel = new UserModel();
        $userInfo = $UserModel->findInfo(['id' => $uid], 'id,share_id,username,grade_id,nick_name,phone,avatar_url,status,openid');
        $token = apiSignToken($userInfo['id']);
        $userInfo['nick_name'] = EmojiDecode($userInfo['nick_name']);
        $data = array();
        $data['token'] = $token;
        $data['userInfo'] = $userInfo;
        return JsonUtils::successful('操作成功', $data);
    }

    /**
     * 检查分享人数是否可以达到分享人可以升级
     * $uid 分享用户id
     * User: hao
     */
    public function share_num($uid)
    {
        $User = new UserModel();

        //上级二级推荐人数累加
        $res = $User->setDataInc(['id'=>$uid],'team_num',1);
        if (!$res){
            return ['code'=>false,'msg'=>'累计分享人数失败'];
        }
        $two_share = $User->getValues(['id'=>$uid],'share_id');
        if ($two_share){
            $res = $User->setDataInc(['id'=>$two_share],'team_num',1);
            if (!$res){
                return ['code'=>false,'msg'=>'累计分享人数失败'];
            }
        }

        $count = $User->statInfo(['share_id' => $uid]);

        $UserGrade = new UserGradeModel();
        $where = array();
        $where[] = ['status', '=', 1];
        $where[] = ['is_shareholder', '=', 0];
        $where[] = ['share_num', '<=', $count];

        $grade = $UserGrade->where($where)->order('share_num desc')->field('id,share_num')->find();
        $grade_id = $User->getValues(['id' => $uid], 'grade_id');
        if ($grade && $grade['id'] > $grade_id) {
            //分享人升级
            $User->updateInfo(['id' => $uid], ['grade_id' => $grade['id']]);

            $upgradeMode = new UserUpgradeLogModel();
            $up_data = array();
            $up_data['uid'] = $uid;
            $up_data['grade_id'] = $grade['id'];
            $up_data['type'] = 1;
            $up_data['create_time'] = time();
            $up_data['remarks'] = '满足推广人数:' . $grade['share_num'];
            $upgradeMode->addInfo($up_data);
        }

    }


}