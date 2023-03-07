<?php

namespace App\Jobs;

class AsyncToEsJob extends Job
{
    /**
     * 任务可以尝试的最大次数。
     *
     * @var int
     */
    public $tries = 2;


    private $columns;
    private $type;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($columns,$type=0)
    {
        $this->columns = $columns;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        var_dump($this->column->getName());
        var_dump($this->columns->getValue());
        var_dump($this->columns->getUpdated());
    }
}
