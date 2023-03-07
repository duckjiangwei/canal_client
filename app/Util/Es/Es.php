<?php
/**
 * ES实例工具类
 */

namespace App\Http\Util;

use Elasticsearch\ClientBuilder;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Es
{
    private static $client = null; //静态实例

    /**
     * 构造方法：初始化ES
     *
     * @return object
     */
    private function __construct()
    {
        //配置日志
        $logger = new Logger('monolog');
        $logger->pushHandler(new StreamHandler(storage_path('logs/elasticsearch.log'), Logger::WARNING));

        //设置连接信息
        $hosts = [
            [
                'host' => config('es.host'),
                'port' => config('es.port'),
            ]
        ];

        //创建实例
        self::$client = ClientBuilder::create()    // 实例化 ClientBuilder
                    ->setHosts($hosts)      // 设置主机信息
                    ->setLogger($logger)    // 设置日志
                    ->build();              // 构建客户端对象

    }

    /**
     * 获取静态实例
     *
     * @return object
     */
    public static function getEs()
    {
        if (!self::$client) {
            new self;
        }

        return self::$client;
    }

    /*
     * 禁止clone
     */
    private function __clone(){}

}
