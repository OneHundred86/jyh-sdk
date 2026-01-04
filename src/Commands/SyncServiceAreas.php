<?php

namespace Oh86\JYH\Commands;

use Oh86\JYH\Exceptions\PrivateApiException;
use Oh86\JYH\Exceptions\SyncDataException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Oh86\JYH\SyncDatas\AbstractSyncServiceAreas;

class SyncServiceAreas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jyh:sync_service_areas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '从集约化平台同步service_area数据';

    protected ?AbstractSyncServiceAreas $syncService;

    /**
     * @param AbstractSyncServiceAreas|null $syncService
     */
    public function __construct(?AbstractSyncServiceAreas $syncService)
    {
        $this->syncService = $syncService;
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * @return int
     * @throws SyncDataException
     * @throws PrivateApiException
     */
    public function handle(): int
    {
        if (!$this->syncService) {
            Log::debug("无须同步service_area数据");
            return Command::FAILURE;
        }

        Log::debug(__METHOD__, ["starting" => 1]);
        $this->syncDatasFromJyh();
        Log::debug(__METHOD__, ["finished" => 1]);
        return Command::SUCCESS;
    }

    /**
     * @throws SyncDataException
     * @throws PrivateApiException
     */
    protected function syncDatasFromJyh(): void
    {
        $this->syncService->syncDatas();
    }
}
