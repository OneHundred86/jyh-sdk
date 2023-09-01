<?php

namespace Oh86\JYH\OAuth;

use Illuminate\Http\Request;
use Oh86\JYH\Exceptions\LoginException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Oh86\JYH\Utils\UrlUtil;

abstract class AbstractOAuthService
{
    private string $rootUrl;
    private string $app;
    private ?int $siteAppId;
    private ?array $serviceAreaIds;

    private string $appLoginRoute = "";
    private string $appLogoutRoute = "";
    private string $loginCallbackRoute = "";
    private string $logoutCallbackRoute = "";
    private string $fetchUserType = "detail";
    private array $middlewares = [];

    /**
     * @param string $rootUrl
     * @param string $app
     * @param int|null $siteAppId
     */
    public function __construct(string $rootUrl, string $app, ?int $siteAppId = null, ?array $serviceAreaIds = null)
    {
        $this->rootUrl = $rootUrl;
        $this->app = $app;
        $this->siteAppId = $siteAppId;
        $this->serviceAreaIds = $serviceAreaIds;
    }

    /**
     * @return string
     */
    public function getAppLoginRoute(): string
    {
        return $this->appLoginRoute;
    }

    /**
     * @param string $appLoginRoute
     */
    public function setAppLoginRoute(string $appLoginRoute): void
    {
        $this->appLoginRoute = $appLoginRoute;
    }

    /**
     * @return string
     */
    public function getAppLogoutRoute(): string
    {
        return $this->appLogoutRoute;
    }

    /**
     * @param string $appLogoutRoute
     */
    public function setAppLogoutRoute(string $appLogoutRoute): void
    {
        $this->appLogoutRoute = $appLogoutRoute;
    }

    /**
     * @return string
     */
    public function getLoginCallbackRoute(): string
    {
        return $this->loginCallbackRoute;
    }

    /**
     * @param string $loginCallbackRoute
     */
    public function setLoginCallbackRoute(string $loginCallbackRoute): void
    {
        $this->loginCallbackRoute = $loginCallbackRoute;
    }

    /**
     * @return string
     */
    public function getLogoutCallbackRoute(): string
    {
        return $this->logoutCallbackRoute;
    }

    /**
     * @param string $logoutCallbackRoute
     */
    public function setLogoutCallbackRoute(string $logoutCallbackRoute): void
    {
        $this->logoutCallbackRoute = $logoutCallbackRoute;
    }

    /**
     * @return string
     */
    public function getFetchUserType(): string
    {
        return $this->fetchUserType;
    }

    /**
     * @param string $fetchUserType  detail | base
     */
    public function setFetchUserType(string $fetchUserType): void
    {
        $this->fetchUserType = $fetchUserType;
    }

    /**
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middleware;
    }

    /**
     * @param array $middlewares
     */
    public function setMiddlewares(array $middlewares): void
    {
        $this->middleware = $middlewares;
    }


    /**
     * 获取唤起统一登录的url
     * @param string|null $redirectUri eg: /jyh/login
     * @param string|null $state
     * @return string
     */
    public function getLoginUrl(?string $redirectUri, ?string $state): string
    {
        $redirectUri = $redirectUri ?? $this->getLoginCallbackRoute();
        $redirectUri = UrlUtil::genAppUrl($redirectUri);

        return sprintf("%s/usercenter/login?%s", $this->rootUrl, http_build_query([
            "appid" => $this->app,
            "redirect_uri" => $redirectUri,
            "state" => $state,
        ]));
    }

    /**
     * @throws LoginException
     * @return array{user: array, logout_token: string}
     */
    public function getBaseUserByAccessToken(string $accessToken): array
    {
        $url = sprintf("%s/api/oauth/user/base", $this->rootUrl);
        $resp = Http::acceptJson()->post($url, ["access_token" => $accessToken]);

        $arr = $resp->json();
        if(data_get($arr, "errcode") !== 0){
            if(($errmessage = data_get($arr, "errmessage"))){
                throw new LoginException($errmessage);
            }else{
                Log::error(__METHOD__, ["response" => $resp->body()]);
                throw new LoginException("获取用户信息失败");
            }
        }

        return $arr["data"];
    }

    /**
     * @param string $accessToken
     * @return array{user: array, service_area_list: ?array, org_list: ?array, site_list: ?array, user_wx: ?array, logout_token: string}
     * @throws LoginException
     */
    public function getDetailUserByAccessToken(string $accessToken): array
    {
        $url = sprintf("%s/api/oauth/user/detail", $this->rootUrl);
        $resp = Http::acceptJson()->post($url, [
            "access_token" => $accessToken,
            "site_app_id" => $this->siteAppId,
            "service_area_ids" => $this->serviceAreaIds ? implode(",", $this->serviceAreaIds) : null,
        ]);

        $arr = $resp->json();
        if(data_get($arr, "errcode") !== 0){
            if(($errmessage = data_get($arr, "errmessage"))){
                throw new LoginException($errmessage);
            }else{
                Log::error(__METHOD__, ["response" => $resp->body()]);
                throw new LoginException("获取用户信息失败");
            }
        }

        return $arr["data"];
    }

    /**
     * @throws LoginException
     * @return array{user: array, logout_token: string}
     * @return array{user: array, service_area_list: ?array, org_list: ?array, site_list: ?array, user_wx: ?array, logout_token: string}
     */
    public function getUserInfoByAccessToken(string $accessToken): array
    {
        if($this->getFetchUserType() == "base"){
            return $this->getBaseUserByAccessToken($accessToken);
        }else{
            return $this->getDetailUserByAccessToken($accessToken);
        }
    }

    /**
     * 子应用登出后，通知授权中心登出
     * @param string $logoutToken
     * @return bool
     */
    public function logoutJyh(string $logoutToken): bool
    {
        $url = sprintf("%s/usercenter/logout", $this->rootUrl);
        $resp = Http::acceptJson()->post($url, ["logout_token" => $logoutToken]);
        if($resp->json("errcode") !== 0){
            Log::error(__METHOD__, ["response" => $resp->body()]);
            return false;
        }
        return true;
    }

    /**
     * 处理来自统一登录的回调请求
     * @param Request $request
     * @param array $userData
     * @return string  redirectUri登录后的跳转地址
     */
    abstract public function handleLoginCallback(Request $request, Array $userData): string;

    /**
     * 处理本应用系统的登出逻辑，并返回之后的跳转地址
     * @param Request $request
     * @return string
     */
    abstract public function handleAppLogout(Request $request): string;

    /**
     * 处理来自统一登出的回调请求
     * @param Request $request
     * @throws \Exception
     */
    abstract public function handleLogoutCallback(Request $request);
}