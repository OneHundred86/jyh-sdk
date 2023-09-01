<?php

namespace Oh86\JYH\SyncDatas;

use Oh86\JYH\Exceptions;
use Illuminate\Support\Facades\Cache;

abstract class AbstractSyncUsers extends AbstractSyncDatas
{
    public function getSyncDataVersion(): ?int
    {
        return Cache::get("jyhSyncUserVersion");
    }

    protected function setSyncDataVersion(int $version): void
    {
        Cache::put("jyhSyncUserVersion", $version);
    }

    /**
     * @param array{site_app_id: ?int, service_area_id: ?int, from_version: int, sync_option: string, sync_period_id: string} $params
     * @return array{list: array, is_finished: bool}
     * @throws Exceptions\PrivateApiException
     */
    public function getChunkList(array $params): array
    {
        return $this->api->getSyncChunkUserList($params);
    }

    /**
     * @param array $info user
     * @return void
     */
    abstract public function handleCreateOrUpdateData(array $info): void;

    /**
     * @param array $info user
     * @return void
     */
    abstract public function handleDeleteData(array $info): void;
}