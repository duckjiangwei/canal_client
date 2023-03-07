<?php

namespace App\Console\Commands;

use Com\Alibaba\Otter\Canal\Protocol\EntryType;
use Com\Alibaba\Otter\Canal\Protocol\EventType;
use Com\Alibaba\Otter\Canal\Protocol\RowChange;
use Com\Alibaba\Otter\Canal\Protocol\RowData;
use Illuminate\Console\Command;
use xingwenge\canal_php\CanalConnectorFactory;
use xingwenge\canal_php\CanalClient;
use xingwenge\canal_php\Fmt;
use App\Util\Es\EsClient;
use App\Jobs\AsyncToEsJob;
use Illuminate\Support\Facades\Redis;

class BinlogToMq extends Command
{
    /**
     * 控制台命令 signature 的名称。
     *
     * @var string
     */
    protected $signature = 'binlog_to_es';

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
            $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SOCKET_CLUE);
            # $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SWOOLE);

            $client->connect(config('canal_server.canal_server'));
            $client->checkValid();
            $client->subscribe("100", config('canal_server.canal_server_destination'));
            # $client->subscribe("1001", "example", "db_name.tb_name"); # 设置过滤

            while (true) {
                $message = $client->get(100);
                if ($entries = $message->getEntries()) {
                    foreach ($entries as $entry) {
//                        Fmt::println($entry);
                        $this->push($entry);
                    }
                }
            }

            $client->disConnect();
        } catch (\Exception $e) {
            echo $e->getMessage(), PHP_EOL;
        }
    }

    private function push($entry)
    {
        switch ($entry->getEntryType()) {
            case EntryType::TRANSACTIONBEGIN:
            case EntryType::TRANSACTIONEND:
                return;
                break;
        }

        $rowChange = new RowChange();
        $rowChange->mergeFromString($entry->getStoreValue());
        $evenType = $rowChange->getEventType();
        $header = $entry->getHeader();

        echo sprintf("================> binlog[%s : %d],name[%s,%s], eventType: %s", $header->getLogfileName(),
            $header->getLogfileOffset(), $header->getSchemaName(), $header->getTableName(),
            $header->getEventType()), PHP_EOL;
        echo $rowChange->getSql(), PHP_EOL;
        //库名
        $schemaName = $header->getSchemaName();
        //表名
        $tableName = $header->getTableName();

        /** @var RowData $rowData */
        foreach ($rowChange->getRowDatas() as $rowData) {
            switch ($evenType) {
                case EventType::DELETE:
//                    dispatch(new AsyncToEsJob($rowData->getBeforeColumns(),$header,$evenType));
                    $beforeColumns = $rowData->getBeforeColumns();
                    $data = $this->getDataFormat($beforeColumns);
                    break;
                case EventType::INSERT:
                    //
                    $afterColumns = $rowData->getAfterColumns();
                    $data = $this->getDataFormat($afterColumns);
                    break;
                default:
                    $afterColumns = $rowData->getAfterColumns();
                    $data = $this->getDataFormat($afterColumns);
                    break;
            }
            //投递到mq
            dispatch(new AsyncToEsJob($data, $schemaName, $tableName, $evenType));
        }
    }

    private function getDataFormat($columnsObj): array
    {
        $data = [];
        foreach ($columnsObj as $column) {
            array_push($data, [
                'name'   => $column->getName(),
                'value'  => $column->getValue(),
                'update' => var_export($column->getUpdated(), true)
            ]);
        }
        return $data ?? [];
    }
}
