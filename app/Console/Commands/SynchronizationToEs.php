<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use xingwenge\canal_php\CanalConnectorFactory;
use xingwenge\canal_php\CanalClient;
use xingwenge\canal_php\Fmt;
use App\Util\Es\EsClient;

class SynchronizationToEs extends Command
{
    /**
     * 控制台命令 signature 的名称。
     *
     * @var string
     */
    protected $signature = 'synchronization_to_es';

    /**
     * 控制台命令说明。
     *
     * @var string
     */
    protected $description = '同步数据到es';

    /**
     * 执行控制台命令。
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $esClient = EsClient::getEs();
            print_r($esClient);die;
            $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SOCKET_CLUE);
            # $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SWOOLE);

            $client->connect(config('canal_server.canal_server'));
            $client->checkValid();
            $client->subscribe("1001", config('canal_server.canal_server_destination'));
            # $client->subscribe("1001", "example", "db_name.tb_name"); # 设置过滤

            while (true) {
                $message = $client->get(100);
                if ($entries = $message->getEntries()) {
                    foreach ($entries as $entry) {
                        Fmt::println($entry);
                    }
                }
                sleep(1);
            }

            $client->disConnect();
        } catch (\Exception $e) {
            echo $e->getMessage(), PHP_EOL;
        }
    }

}
