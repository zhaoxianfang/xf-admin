<?php
// +----------------------------------------------------------------------
// | redis 队列任务 配置
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ZhaoXianFang <1748331509@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-11-21
// +----------------------------------------------------------------------

namespace app\common\queue;

use app\facade\DocErr;
use util\Redis as RedisClass;

/**
 * 报表统计消息队列模型
 */
class Redis
{
    /**
     * @var object 对象实例
     */
    protected static $instance;

    /**
     * redis工具类对象
     *
     * @var object
     */
    protected $redis;

    /**
     * 报表统计消息队列键名
     *
     * @var string
     */
    protected $key = 'syncqueue';

    /**
     * 初始化
     */
    public function __construct()
    {
        try {
            $redis = RedisClass::getInstance(config('redis.'), config('redis.'));
        } catch (\Exception $e) {
            DocErr::insert([
                'create_time' => time(),
                'type'        => 10,
                'content'     => 'Redis 连续失败！',
            ]);

            throw new \Exception($e->getMessage());
        }

        // $redis = new \Redis();
        // $redis->connect(config('redis.host'), config('redis.port'));
        // $redis->auth(config('redis.auth'));

        $this->redis = $redis;
    }

    public static function init()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * 获取队列key值
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * 获取redis对象
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * 数据写入redis
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-16
     * @param    string       $data [description]
     * @return   [type]             [description]
     */
    public function push($data = '')
    {
        return $this->redis->rPush($this->key, json_encode($data));
    }

    /**
     * 设置redis key
     * @Author   ZhaoXianFang
     * @DateTime 2018-04-19
     * @param    string       $keyName [description]
     */
    public function setKay($keyName = '')
    {
        if ($keyName) {
            $this->key = $keyName;
        }

        return $this;
    }

    /**
     * 删除hash表中指定字段 ,支持批量删除
     * @Author   ZhaoXianFang
     * @DateTime 2018-04-19
     * @param    [type]       $field [多个用英文逗号隔开]
     * @return   [type]              [description]
     */
    public function hDel($field)
    {
        $fieldArr = explode(',', $field);
        $delNum   = 0;
        foreach ($fieldArr as $row) {
            $row = trim($row);
            $delNum += $this->redis->hDel($this->key, $row);
        }
        return $delNum;
    }

    /**
     * 对象的不存在的实例方法进行“调用”时，调用redis中的方法
     * @Author   ZhaoXianFang
     * @DateTime 2017-08-16
     * @param    [type]       $method 方法
     * @param    [type]       $args       参数 数组
     * @return   [type]                   [description]
     */
    public function __call($method, $args)
    {
        // 捕获异常
        try {
            // dump($args);die;
            return call_user_func_array(array($this->redis, $method), $args);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
            // $this->error($e->getMessage());
        }
    }

    /**
     * 当对这个类的不存在的静态方法进行“调用”
     * @Author   ZhaoXianFang
     * @DateTime 2017-08-16
     * @param    [type]       $method 方法
     * @param    [type]       $args       [description]
     * @return   [type]                   [description]
     */
    public static function __callstatic($method, $args)
    {
        // 捕获异常
        try {
            return call_user_func_array(array($this->redis, $method), $args);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
            // $this->error($e->getMessage());
        }
    }

}
