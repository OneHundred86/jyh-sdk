<?php

namespace Oh86\JYH\Commands;

use Oh86\JYH\Exceptions\PrivateApiException;
use Oh86\JYH\Exceptions\SyncDataException;
use Oh86\JYH\SyncDatas\AbstractSyncUsers;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jyh:sync_users {sync_option : all | inc}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '从JYH平台同步用户数据';

    protected ?AbstractSyncUsers $syncUsersService;

    public function __construct(?AbstractSyncUsers $syncUsersService)
    {
        parent::__construct();
        $this->syncUsersService = $syncUsersService;
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
        if(!$this->syncUsersService){
            Log::debug("无须同步jyh用户数据");
            return Command::FAILURE;
        }

        $syncOption = $this->argument("sync_option");
        Log::debug(__METHOD__, ["starting" => 1, "sync_option" => $syncOption]);
        $this->syncUsersFromJyh($syncOption);
        Log::debug(__METHOD__, ["finished" => 1, "sync_option" => $syncOption]);
        return Command::SUCCESS;
    }

    /**
     * @throws SyncDataException
     * @throws PrivateApiException
     */
    protected function syncUsersFromJyh(string $syncOption): void
    {
        $this->syncUsersService->syncDatas($syncOption);
    }
}
