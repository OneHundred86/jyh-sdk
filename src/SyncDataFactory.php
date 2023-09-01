<?php

namespace Oh86\JYH;

use Oh86\JYH\PrivateApi\UCPrivateApi;
use Oh86\JYH\SyncDatas\AbstractSyncOrgs;
use Oh86\JYH\SyncDatas\AbstractSyncServiceAreas;
use Oh86\JYH\SyncDatas\AbstractSyncSites;
use Oh86\JYH\SyncDatas\AbstractSyncUsers;

class SyncDataFactory
{
    /**
     * @param array $config  jyh.uc
     * @return AbstractSyncUsers
     */
    public static function createSyncUserService(array $config): ?AbstractSyncUsers
    {
        if($clazz = self::getClass(data_get($config, "sync_data.user.service_class"))){
            return new $clazz($config["site_app_id"], $config["service_area_ids"], self::getUCPrivateApi());
        }
        return null;
    }

    /**
     * @param array $config  jyh.uc
     * @return AbstractSyncServiceAreas
     */
    public static function createSyncServiceAreaService(array $config): ?AbstractSyncServiceAreas
    {
        if($clazz = self::getClass(data_get($config, "sync_data.org_struct.service_area_service_class"))){
            return new $clazz(self::getUCPrivateApi());
        }
        return null;
    }

    /**
     * @param array $config  jyh.uc
     * @return AbstractSyncOrgs
     */
    public static function createSyncOrgService(array $config): ?AbstractSyncOrgs
    {
        if($clazz = self::getClass(data_get($config, "sync_data.org_struct.org_service_class"))){
            return new $clazz($config["site_app_id"], $config["service_area_ids"], self::getUCPrivateApi());
        }
        return null;
    }

    /**
     * @param array $config  jyh.uc
     * @return AbstractSyncSites
     */
    public static function createSyncSiteService(array $config): ?AbstractSyncSites
    {
        if($clazz = self::getClass(data_get($config, "sync_data.org_struct.site_service_class"))){
            return new $clazz($config["site_app_id"], $config["service_area_ids"], self::getUCPrivateApi());
        }
        return null;
    }

    protected static function getClass(?string $clazz): ?string
    {
        if (!$clazz){
            return null;
        }

        return "\\" . ltrim($clazz, "\\");
    }

    protected static function getUCPrivateApi(): UCPrivateApi
    {
        return app(UCPrivateApi::class);
    }
}