<?php

namespace Oh86\JYH\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Oh86\JYH\PrivateApi\UCPrivateApi;
use Oh86\JYH\Utils\PrivateApiUtil;
use RuntimeException;

class CheckPrivateApiRequestFromUC
{
    /**
     * Handle an incoming request.
     *
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $this->checkCredentials($request);
        } catch (RuntimeException $e) {
            return ["errcode" => -1, "errmessage" => $e->getMessage()];
        }

        return $next($request);
    }

    protected function checkCredentials(Request $request)
    {
        $app = $request->input("app");
        $time = $request->input("time");
        $token = $request->input("token");

        if (abs(now()->timestamp - $time) >= 300) {
            throw new RuntimeException("时间校验失败");
        }
        $config = config("jyh.uc.private_api");
        if ($app !== $config["app"]) {
            throw new RuntimeException("app校验失败");
        }

        $util = new PrivateApiUtil($app, $config["ticket"]);
        if ($token !== $util->genToken($time)) {
            throw new RuntimeException("token校验失败");
        }
    }
}