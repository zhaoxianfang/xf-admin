<?php
// +---------------------------------------------------------------------
// | ES工厂
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\common\elastic\lib;

use Elasticsearch\ClientBuilder;

/**
 * 实例化es-php客户端工厂类
 */
class ElasticsearchFactory
{
    /**
     * @var object es实例
     */
    private static $instance;

    /**
     * @var array 主机配置信息
     */
    private static $hosts;

    /**
     * 禁止实例化
     */
    private function __construct()
    {

    }

    /**
     * 禁止克隆
     */
    private function __clone()
    {

    }

    /**
     * 初始化
     */
    private static function init()
    {
        self::$hosts = config('es.host');
    }

    /**
     * 获取es实例
     * @return object es-php客户端实例
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::init();

            $client_builder = ClientBuilder::create();

            $hosts = [self::$hosts];

            $client_builder->setHosts($hosts);
            $client = $client_builder->build();

            self::$instance = $client;
        }

        return self::$instance;
    }

}