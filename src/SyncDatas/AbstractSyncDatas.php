<?php

namespace Oh86\JYH\SyncDatas;

use Illuminate\Support\Facades\Log;
use Oh86\JYH\Constant;
use Oh86\JYH\Exceptions;
use Oh86\JYH\Exceptions\PrivateApiException;
use Oh86\JYH\Exceptions\SyncDataException;
use Oh86\JYH\PrivateApi\UCPrivateApi;

abstract class AbstractSyncDatas
{
    protected ?int $siteAppId;
    protected ?int $serviceAreaId;

    protected UCPrivateApi $api;

    /**
     * @param int|null $siteAppId
     * @param int|null $serviceAreaId
     * @param UCPrivateApi $api
     */
    public function __construct(?int $siteAppId, ?int $serviceAreaId, UCPrivateApi $api)
    {
        $this->siteAppId = $siteAppId;
        $this->serviceAreaId = $serviceAreaId;
        $this->api = $api;
    }

    public function setUCPrivateApi(UCPrivateApi $api): void
    {
        $this->api = $api;
    }

    abstract function getSyncDataVersion(): ?int;

    abstract protected function setSyncDataVersion(int $version): void;

    /**
     * @param array{site_app_id: ?int, service_area_id: ?int, from_version: int, sync_option: string, sync_period_id: string} $params
     * @return array{list: array, is_finished: bool, version: int}
     * @throws Exceptions\PrivateApiException
     */
    abstract public function getChunkList(array $params): array;

    /**
     * @param array $info user / org / site
     * @return void
     */
    abstract public function handleCreateOrUpdateData(array $info): void;

    /**
     * @param array $info user / org / site
     * @return void
     */
    abstract public function handleDeleteData(array $info): void;

    /**
     * @param string $syncOption : inc | all
     * @return void
     * @throws SyncDataException
     * @throws PrivateApiException
     */
    public function syncDatas(string $syncOption): void
    {
        $syncPeriodId = uniqid();
        $syncName = $this->getSyncName();

        if ($syncOption == "all") {
            $fromVersion = 0;
        } else {
            $fromVersion = $this->getSyncDataVersion();
            if ($fromVersion === null) {
                throw new SyncDataException($syncName . "数据先至少完成一次全量同步");
            }
        }

        while (true) {
            $data = $this->getChunkList([
                "site_app_id" => $this->siteAppId,
                "service_area_id" => $this->serviceAreaId,
                "from_version" => $fromVersion,
                "sync_option" => $syncOption,
                "sync_period_id" => $syncPeriodId,
            ]);

            $version = $data['version'];
            $isFinished = $data['is_finished'];
            $list = $data['list'];

            Log::debug(__METHOD__, ["sync" => $syncName, "sync_option" => $syncOption, "from_version" => $fromVersion, "to_version" => $version]);

            foreach ($list as $item) {
                $operation = $item["operation"];

                if ($this->isSyncUser()) {
                    unset($item["operation"]);
                    if ($operation == Constant::SyncOpDelete) {
                        $this->handleDeleteData($item);
                    } else {
                        $this->handleCreateOrUpdateData($item);
                    }
                } else {
                    if ($operation == Constant::SyncOpDelete) {
                        $this->handleDeleteData($item["info"]);
                    } else {
                        $this->handleCreateOrUpdateData($item["info"]);
                    }
                }
            }

            $fromVersion = $version;
            if ($syncOption == "inc") {
                $this->setSyncDataVersion($version);
            } elseif ($syncOption == "all") {
                if ($isFinished) {
                    $this->setSyncDataVersion($version);
                }
            }

            if ($isFinished) {
                break;
            }
        }
    }

    protected function getSyncName(): string
    {
        if ($this instanceof AbstractSyncUsers) {
            return "用户";
        } elseif ($this instanceof AbstractSyncOrgs) {
            return "org";
        } elseif ($this instanceof AbstractSyncSites) {
            return "site";
        }

        return "";
    }

    protected function isSyncUser(): bool
    {
        if ($this instanceof AbstractSyncUsers) {
            return true;
        }
        return false;
    }
}