<?php

namespace Oh86\JYH;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Oh86\JYH\Commands\SyncOrgs;
use Oh86\JYH\Commands\SyncServiceAreas;
use Oh86\JYH\Commands\SyncSites;
use Oh86\JYH\Commands\SyncUsers;
use Oh86\JYH\Controllers\JYHController;
use Oh86\JYH\Middlewares\CheckPrivateApiRequestFromUC;
use Oh86\JYH\OAuth\AbstractOAuthService;
use Oh86\JYH\PrivateApi\UCPrivateApi;
use Oh86\JYH\SyncDatas\AbstractSyncOrgs;
use Oh86\JYH\SyncDatas\AbstractSyncServiceAreas;
use Oh86\JYH\SyncDatas\AbstractSyncSites;
use Oh86\JYH\SyncDatas\AbstractSyncUsers;

class JYHServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->publishConfig();
        $this->loadConfigs();

        $this->loadOAuthService();
        $this->loadSyncDataServices();
        $this->loadPrivateApis();
    }

    public function boot(): void
    {
        // 加载命令
        $this->loadCommands();

        // 加载路由
        $this->loadRoutes();
    }

    protected function loadConfigs(): void
    {
        $this->mergeConfigFrom(__DIR__ . "/../config/jyh.php", "jyh");
    }

    protected function publishConfig()
    {
        if($this->app->runningInConsole()){
            $this->publishes([
                __DIR__ . "/../config/jyh.php" => config_path('jyh.php'),
            ]);
        }
    }

    protected function loadOAuthService()
    {
        $this->app->singleton(AbstractOAuthService::class, function(){
            $config = config("jyh.uc");
            if($service = OAuthFactory::createService($config)){
                $service->setAppLoginRoute($config["oauth"]["app_login_route"]);
                $service->setAppLogoutRoute($config["oauth"]["app_logout_route"]);
                $service->setLoginCallbackRoute($config["oauth"]["login_callback_route"]);
                $service->setLogoutCallbackRoute($config["oauth"]["logout_callback_route"]);
                $service->setFetchUserType($config["oauth"]["fetch_user_type"]);
                $service->setMiddlewares($config["oauth"]["middlewares"]);
            }
            return $service;
        });
    }

    protected function loadSyncDataServices()
    {
        $this->app->singleton(AbstractSyncUsers::class, function (){
            $config = config("jyh.uc");
            return SyncDataFactory::createSyncUserService($config);
        });

        $this->app->singleton(AbstractSyncServiceAreas::class, function (){
            $config = config("jyh.uc");
            return SyncDataFactory::createSyncServiceAreaService($config);
        });

        $this->app->singleton(AbstractSyncOrgs::class, function (){
            $config = config("jyh.uc");
            return SyncDataFactory::createSyncOrgService($config);
        });

        $this->app->singleton(AbstractSyncSites::class, function (){
            $config = config("jyh.uc");
            return SyncDataFactory::createSyncSiteService($config);
        });
    }

    protected function loadPrivateApis()
    {
        $this->app->bind(UCPrivateApi::class, function (){
            $config = config("jyh.uc");
            return new UCPrivateApi($config["root_url"], $config["private_api"]["app"], $config["private_api"]["ticket"]);
        });
    }

    protected function loadCommands(): void
    {
        $this->commands([
            SyncUsers::class,
            SyncServiceAreas::class,
            SyncOrgs::class,
            SyncSites::class,
        ]);
    }

    protected function loadRoutes()
    {
        $config = config("jyh.uc");
        
        // 登录登出
        if($config["oauth"]["service_class"]){
            /** @var AbstractOAuthService $service */
            $service = $this->app->get(AbstractOAuthService::class);
            if ($service){
                Route::get($service->getAppLoginRoute(), [JYHController::class, "appLogin"]);
                Route::middleware($service->getMiddlewares())->get($service->getLoginCallbackRoute(), [JYHController::class, "loginCallback"]);
                Route::middleware($service->getMiddlewares())->any($service->getAppLogoutRoute(), [JYHController::class, "appLogout"]);
                Route::any($service->getLogoutCallbackRoute(), [JYHController::class, "logoutCallback"]);
            }
        }

        // 同步用户
        $syncUserConfig = $config["sync_data"]["user"];
        if($syncUserConfig["service_class"]){
            Route::middleware(CheckPrivateApiRequestFromUC::class)
                ->any($syncUserConfig["route"], [JYHController::class, "syncUsers"]);
        }
        // 同步机构
        $syncOrgStructConfig = $config["sync_data"]["org_struct"];
        if($syncOrgStructConfig["service_area_service_class"] || $syncOrgStructConfig["org_service_class"] || $syncOrgStructConfig["site_service_class"]) {
            Route::middleware(CheckPrivateApiRequestFromUC::class)
                ->any($syncOrgStructConfig["route"], [JYHController::class, "syncOrgStructs"]);
        }
        
    }
}