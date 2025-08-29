<?php

return [
    "uc" => [
        "root_url" => env("JYH_UC_ROOT_URL", "https://cloudtest.southxx.com"),
        "site_app_id" => env("JYH_SITE_APPID"),
        "service_area_ids" => env("JYH_SERVICE_AREA_IDS") ? explode(",", env("JYH_SERVICE_AREA_IDS")) : null,
        "private_api" => [
            "app" => env("JYH_UC_PRIVATE_APP", "demo"),
            "ticket" => env("JYH_UC_PRIVATE_TICKET", "demo-ticket"),
        ],
        "oauth" => [
            "app" => env("JYH_OAUTH_APP", "demo"),
            // 统一登录和登出的业务处理类，需要继承类\Oh86\JYH\OAuth\AbstractOAuthService，null表示不需要接入
            "service_class" => null,
            // 本应用系统的登录路由，无须指定域名
            "app_login_route" => env("JYH_OAUTH_APP_LOGIN_URL", "/login"),
            // 本应用系统的登出路由，无须指定域名
            "app_logout_route" => env("JYH_OAUTH_APP_LOGOUT_URL", "/logout"),
            // 用户中心授权登录后的重定向到本应用的url，自动拼接app.url，对应申请表的redirect_uri
            "login_callback_route" => env("JYH_OAUTH_REDIRECT_URI", "/jyh/login"),
            // 用户中心登出后请求本应用的url，自动拼接app.url，对应申请表的logout_callback_url
            "logout_callback_route" => env("JYH_OAUTH_LOGOUT_CALLBACK_URL", "/jyh/logout"),
            // 授权登录类型，detail / base，detail表示获取详细的用户数据和权限数据，base表示只获取基本的用户数据
            "fetch_user_type" => env("JYH_OAUTH_FETCH_USER_TYPE", "detail"),
            "middlewares" => ["web"],
        ],
        "sync_data" => [
            "user" => [
                // 用户同步的业务处理类，需要继承类\Oh86\JYH\SyncDatas\AbstractSyncUsers，null表示不需要接入用户同步
                "service_class" => null,
                // 用户中心发起同步用户操作时调用的url，自动拼接app.url，对应申请表的sync_user_url
                "route" => env("JYH_SYNC_USER_URL", "/jyh/sync/users"),
            ],
            "org_struct" => [
                // 用户中心发起同步机构操作时调用的url，自动拼接app.url，对应申请表的sync_org_struct_url
                "route" => env("JYH_SYNC_ORG_STRUCT_URL", "/jyh/sync/org_structs"),
                // service_area同步的业务处理类，需要继承类\Oh86\JYH\SyncDatas\AbstractSyncServiceAreas，null表示不需要接入service_area同步
                "service_area_service_class" => null,
                // org同步的业务处理类，需要继承类\Oh86\JYH\SyncDatas\AbstractSyncOrgs，null表示不需要接入org同步
                "org_service_class" => null,
                // site同步的业务处理类，需要继承类\Oh86\JYH\SyncDatas\AbstractSyncSites，null表示不需要接入site同步
                "site_service_class" => null,
            ],
        ],
    ],
];