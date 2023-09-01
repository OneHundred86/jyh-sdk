<?php

namespace Oh86\JYH\PrivateApi;

use Oh86\JYH\Exceptions\PrivateApiException;

class UCPrivateApi extends PrivateApiBase
{
    /**
     * @param array{site_app_id: ?int, service_area_id: ?int, from_version: int, sync_option: string, sync_period_id: string} $params
     * @return array{list: array, is_finished: bool, version: int}
     * @throws PrivateApiException
     */
    public function getSyncChunkUserList(array $params): array
    {
        $api = "/api/private/sync/chunk/user/list";
        $arr = $this->post($api, $params);

        return $arr["data"];
    }

    /**
     * @param array{site_app_id: ?int, service_area_id: ?int, from_version: int, sync_option: string, sync_period_id: string} $params
     * @return array{list: array, is_finished: bool, version: int}
     * @throws PrivateApiException
     */
    public function getSyncChunkOrgList(array $params): array
    {
        $arr = $this->post("api/private/sync/chunk/org/list", $params);
        return $arr["data"];
    }

    /**
     * @param array{site_app_id: ?int, service_area_id: ?int, from_version: int, sync_option: string, sync_period_id: string} $params
     * @return array{list: array, is_finished: bool, version: int}
     * @throws PrivateApiException
     */
    public function getSyncChunkSiteList(array $params): array
    {
        $arr = $this->post("api/private/sync/chunk/site/list", $params);
        return $arr["data"];
    }

    /**
     * @param array $params
     * @return array{total: int, list: array}
     * @throws PrivateApiException
     */
    public function getSyncServiceAreaList(array $params = []): array
    {
        $arr = $this->post("/api/private/sync/servicearea/list", $params);
        return $arr["data"];
    }
}