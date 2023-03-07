<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Com\Alibaba\Otter\Canal\Protocol\EventType;

class AsyncToEsJob extends Job
{
    /**
     * 任务可以尝试的最大次数。
     *
     * @var int
     */
    public $tries = 2;


    private $data;
    private $schemaName;
    private $tableName;
    private $evenType;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $schemaName, $tableName, $evenType = 0)
    {
        $this->data = $data;
        $this->schemaName = $schemaName;
        $this->tableName = $tableName;
        $this->evenType = $evenType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //记日志
        Log::info('job',['data'=>$this->data,'schemaName'=>$this->schemaName,'evenType'=>$this->evenType]);
    }
}
