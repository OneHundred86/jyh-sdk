<?php

namespace Oh86\JYH\SyncDatas;

use Oh86\JYH\Exceptions;
use Oh86\JYH\Exceptions\PrivateApiException;
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
     * 处理同步数据方式一：一个一个处理，缺点无法删除废弃的旧数据
     * @param array{id: int, name: string, sort: int} $data  service_area
     * @return void
     */
    public function handleSyncData(array $data)
    {

    }

    /**
     * 处理同步数据方式二（建议使用该种方式）：一次性处理所有数据
     * @param array{id: int, name: string, sort: int}[] $allDatas  service_area arrays
     * @return void
     */
    public function handleSyncDatas($allDatas)
    {

    }

    /**
     * @return void
     * @throws PrivateApiException
     */
    public function syncDatas(): void
    {
        $data = $this->getDataList();
        $this->handleSyncDatas($data["list"]);
        foreach ($data["list"] as $info) {
            $this->handleSyncData($info);
        }
    }
}