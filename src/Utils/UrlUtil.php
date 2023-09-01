<?php

namespace Oh86\JYH\Utils;

class UrlUtil
{
    /**
     * 生成本应用带域名的完整url
     * @param string $uri  没有带域名的路径
     * @param array $params
     * @return string
     */
    public static function genAppUrl(string $uri, array $params = []): string
    {
        $rootUrl = config("app.url");
        $appUrl = rtrim($rootUrl, "/") . "/" . ltrim($uri, "/");

        if($params){
            return $appUrl . "?" . http_build_query($params);
        }else{
            return $appUrl;
        }
    }
}