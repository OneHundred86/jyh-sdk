<?php

namespace Oh86\JYH;

use Oh86\JYH\OAuth\AbstractOAuthService;

class OAuthFactory
{
    /**
     * @param array $config  jyh.uc
     * @return AbstractOAuthService|null
     */
    public static function createService(array $config): ?AbstractOAuthService
    {
        $clazz = self::getClass($config);
        if ($clazz){
            return new $clazz($config["root_url"], $config["oauth"]["app"], $config["site_app_id"], $config["service_area_ids"]);
        }
        return null;
    }

    protected static function getClass(array $config): ?string
    {
        $clazz = data_get($config, "oauth.service_class");
        if (!$clazz){
            return null;
        }

        return "\\" . ltrim($clazz, "\\");
    }
}