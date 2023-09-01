<?php

namespace Oh86\JYH\Commands;

use Oh86\JYH\Exceptions\PrivateApiException;
use Oh86\JYH\Exceptions\SyncDataException;
use Oh86\JYH\SyncDatas\AbstractSyncSites;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncSites extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jyh:sync_sites {sync_option : all | inc}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '从JYH平台同步site数据';

    protected ?AbstractSyncSites $syncService;

    public function __construct(?AbstractSyncSites $syncService)
    {
        parent::__construct();
        $this->syncService = $syncService;
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
        if(!$this->syncService){
            Log::debug("无须同步site数据");
            return Command::FAILURE;
        }

        $syncOption = $this->argument("sync_option");
        Log::debug(__METHOD__, ["starting" => 1, "sync_option" => $syncOption]);
        $this->syncDatasFromJyh($syncOption);
        Log::debug(__METHOD__, ["finished" => 1, "sync_option" => $syncOption]);
        return Command::SUCCESS;
    }

    /**
     * @throws SyncDataException
     * @throws PrivateApiException
     */
    protected function syncDatasFromJyh(string $syncOption): void
    {
        $this->syncService->syncDatas($syncOption);
    }
}
