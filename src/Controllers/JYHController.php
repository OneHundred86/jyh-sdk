<?php

namespace Oh86\JYH\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Oh86\JYH\Commands\SyncOrgs;
use Oh86\JYH\Commands\SyncServiceAreas;
use Oh86\JYH\Commands\SyncSites;
use Oh86\JYH\Commands\SyncUsers;
use Oh86\JYH\Exceptions\LoginException;
use Oh86\JYH\Exceptions\SyncDataException;
use Oh86\JYH\OAuth\AbstractOAuthService;

class JYHController
{
    protected AbstractOAuthService $oAuthService;

    /**
     * @param AbstractOAuthService $oAuthService
     */
    public function __construct(AbstractOAuthService $oAuthService)
    {
        $this->oAuthService = $oAuthService;
    }


    public function appLogin(Request $request)
    {
        $url = $this->oAuthService->getLoginUrl($request->input("redirect_uri"), $request->input("state"));
        return redirect()->to($url);
    }

    public function loginCallback(Request $request)
    {
        $accessToken = $request->input("access_token");
        if (!$accessToken) {
            return "access_token为空";
        }

        try {
            $userData = $this->oAuthService->getUserInfoByAccessToken($accessToken);
            $response = $this->oAuthService->handleLoginCallback($request, $userData);
            $logoutToken = $userData["logout_token"];
        } catch (LoginException $e) {
            return $e->getMessage();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        // 如果使用session作为登录状态维护组件，则使用session实现统一登出
        // 否则需要在 handleLoginCallback 自行实现统一登出逻辑
        if (Session::isStarted()) {
            Session::put("jyhLogoutToken", $logoutToken);
            Cache::put("jyhLogoutToken@$logoutToken", Session::getId(), now()->addDay());
        }

        return $response;
    }

    public function appLogout(Request $request)
    {
        // 如果使用session作为登录状态维护组件，则使用session实现统一登出
        // 否则需要在 handleAppLogout 自行实现统一登出逻辑
        if (Session::isStarted()) {
            if ($logoutToken = Session::pull("jyhLogoutToken")) {
                $this->oAuthService->logoutJyh($logoutToken);
            }
        }

        return $this->oAuthService->handleAppLogout($request);
    }

    public function logoutCallback(Request $request)
    {
        $logoutToken = $request->input("logout_token");
        $sessionId = Cache::pull("jyhLogoutToken@{$logoutToken}");

        // 如果使用session作为登录状态维护组件，则使用session实现统一登出
        // 否则需要在 handleLogoutCallback 自行实现统一登出逻辑
        if ($sessionId) {
            Session::setId($sessionId);
            Session::start();
            Session::flush();
            Session::save();
        }

        try {
            $this->oAuthService->handleLogoutCallback($request);
            return ["errcode" => 0, "errmessage" => "ok"];
        } catch (\Exception $e) {
            $code = $e->getCode();
            return ["errcode" => $code == 0 ? -1 : $code, "errmessage" => $e->getMessage()];
        }
    }

    public function syncUsers(Request $request)
    {
        try {
            Artisan::call(SyncUsers::class, ["sync_option" => "inc"]);
        } catch (SyncDataException $e) {
            return ["errcode" => -1, "errmessage" => $e->getMessage()];
        }

        return ["errcode" => 0, "errmessage" => "ok"];
    }

    public function syncOrgStructs(Request $request)
    {
        try {
            Artisan::call(SyncServiceAreas::class);
            Artisan::call(SyncOrgs::class, ["sync_option" => "inc"]);
            Artisan::call(SyncSites::class, ["sync_option" => "inc"]);
        } catch (SyncDataException $e) {
            return ["errcode" => -1, "errmessage" => $e->getMessage()];
        }

        return ["errcode" => 0, "errmessage" => "ok"];
    }
}