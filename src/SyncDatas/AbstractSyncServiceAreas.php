<?php

namespace Oh86\JYH\SyncDatas;

use Oh86\JYH\Exceptions;
use Oh86\JYH\Exceptions\PrivateApiException;
use Oh86\JYH\Exceptions\SyncDataException;
use Oh86\JYH\PrivateApi\UCPrivateApi;

abstract class AbstractSyncServiceAreas
{
    protected UCPrivateApi $api;

    /**
     * @param UCPrivateApi $api
     */
    public function __construct(UCPrivateApi $api)
    {
        $this->api = $api;
    }

    public function setUCPrivateApi(UCPrivateApi $api): void
    {
        $this->api = $api;
    }

    /**
     * @param array $params
     * @return array{total: int, list: array}
     * @throws Exceptions\PrivateApiException
     */
    public function getDataList(array $params = []): array
    {
        return $this->api->getSyncServiceAreaList($params);
    }

    /**
     * @param array{id: int, name: string, sort: int} $info  service_area
     * @return void
     */
    abstract public function handleSyncData(array $info): void;

    /**
     * @return void
     * @throws PrivateApiException
     */
    public function syncDatas(): void
    {
        $data = $this->getDataList();
        foreach($data["list"] as $info){
            $this->handleSyncData($info);
        }
    }
}