<?php


namespace app\common\model;
use think\facade\Cache;

/*
   封装redis的常用命令，方便以后使用

 */
class OperateRedis
{

    // protected $redis;
    public $redis = false;
    public $connectionStatus = false; //redis连接状态 成功/失败

    /**
     * 构造方法。
     *
     * @param \address 连接redis的地址
     * @param \port 连接redis的端口号
     * @param \port 连接redis的密码
     * @author Yzj 2019-12-18
     * @return void
     */
    public function __construct($address='127.0.0.1',$port=6379,$pass='')
    {
        if (!$this->redis) {
            $redis = new \Redis();
            $redis->connect($address, $port); //第三个参数是超过n秒则放弃

            //如果有密码则进行验证，默认不需要密码
            if ($pass) {
                $this->connectionStatus = $redis->auth($pass);//登录验证密码，返回【true | false】
            }

            $this->redis = $redis;
        }

    }


    /**
     * 存储字符串在Redis。
     *
     * @param \key 需要定义的key名
     * @param \value 需要存储的数据
     * @param \time 有效时间，单位为秒，不传的话，数据则默认为不限时存储
     * @author Yzj 2019-12-18
     * @return string
     */
    public function set($key,$value,$time=false)
    {
        if ($time) {
            $res = $this->redis->setex($key,$time,$value); //key=value，有效期为$time秒 [true]
            return $res ? $res : false;
        }

        $res = $this->redis->set($key,$value); //设置key=aa value=1 [true]
        return $res ? $res : false;
    }

    /**
     * 获取Redis的字符串。
     *
     * @param \key 需要获取值的key名
     * @author Yzj 2019-12-18
     * @return string
     */
    public function get($key)
    {
        $res = $this->redis->get($key); //获取redis的key值
        return $res;
    }

    /**
     * 查看key的失效时间。
     *
     * @param \key 需要查询的key名
     * @author Yzj 2019-12-18
     * @return string 秒数，多少秒后失效
     */
    public function ttl($key)
    {
        return $this->redis->ttl($key); //查看失效时间[-1 | timestamps]
    }


    /**
     * 并发锁，单key只能写入一个值。
     * @param key 需要锁的key
     * @param value 需要存储的值
     * @param overTime 过期时间，单位为秒。此值是为了防止并发锁出现死锁，从而影响后面的业务
     * @param exeNum 执行抢锁的次数,如果第一次没抢成功则会循环执行这个次数
     * @param sleepTime 抢锁的间隔时间，单位是微秒。1秒等于1000000
     * @author Yzj 2020-5-9
     * @return bool 成功或失败
     */
    // public function redisLock($key,$value,$overTime=5,$exeNum=4,$sleepTime=300000)
    public function redisLock($key,$value,$overTime=5,$exeNum=120,$sleepTime=10000)
    {
        $res = false;
        while ( $exeNum-- > 0 ) {
            $res = $this->redis->set($key,$value,['NX','EX'=>$overTime]);
            if ($res) {
                break; //获取锁成功，直接跳出循环
            }
            //echo $exeNum.'<br>';
            usleep($sleepTime); //微妙延迟执行 进程挂起
        }
        return $res;
    }

    /**
     * 并发锁进行解锁操作
     * @param key 需要解锁的key
     * @param value 加锁时存储的值，此值是为了预防误删他人的锁
     * @author Yzj 2020-5-9
     * @return bool 成功或失败
     */
    public function redisUnlock($key,$value)
    {
        $check_value = $this->redis->get($key);
        if ($value == $check_value) {
            return $this->redis->del($key);
        }
        return false;
    }





}