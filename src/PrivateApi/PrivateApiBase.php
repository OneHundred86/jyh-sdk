<?php

namespace Oh86\JYH\PrivateApi;

use Oh86\JYH\Exceptions\PrivateApiException;
use Oh86\JYH\Utils\PrivateApiUtil;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class PrivateApiBase
{
    protected string $rootUrl;
    protected string $app;
    protected string $ticket;
    private PendingRequest $request;
    private Response $response;

    public function __construct(string $rootUrl, string $app, string $ticket)
    {
        $this->rootUrl = $rootUrl;
        $this->app = $app;
        $this->ticket = $ticket;
    }

    /**
     * @return string
     */
    public function getRootUrl(): string
    {
        return $this->rootUrl;
    }

    /**
     * @param string $rootUrl
     */
    public function setRootUrl(string $rootUrl): void
    {
        $this->rootUrl = $rootUrl;
    }

    /**
     * @return string
     */
    public function getApp(): string
    {
        return $this->app;
    }

    /**
     * @param string $app
     */
    public function setApp(string $app): void
    {
        $this->app = $app;
    }

    /**
     * @return string
     */
    public function getTicket(): string
    {
        return $this->ticket;
    }

    /**
     * @param string $ticket
     */
    public function setTicket(string $ticket): void
    {
        $this->ticket = $ticket;
    }

    /**
     * @return PendingRequest
     */
    public function getRequest(): PendingRequest
    {
        return $this->request ?? Http::asJson()->acceptJson();
    }

    /**
     * @param PendingRequest $request
     */
    public function setRequest(PendingRequest $request): void
    {
        $this->request = $request;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    public function makeFullUrl(string $api): string
    {
        return sprintf("%s/%s", $this->rootUrl, ltrim($api, "/"));
    }

    public function attachCredential(array $params): array
    {
        $util = new PrivateApiUtil($this->app, $this->ticket);
        return array_merge($params, $util->genCredential(now()->timestamp));
    }

    /**
     * 默认使用json格式发送post请求
     * @throws PrivateApiException
     */
    public function post(string $api, array $params): array
    {
        $url = $this->makeFullUrl($api);
        $params = $this->attachCredential($params);

        $resp = $this->getRequest()->post($url, $params);
        $this->response = $resp;

        return $this->handleResponse($resp);
    }

    /**
     * 发送get请求
     * @throws PrivateApiException
     */
    public function get(string $api, array $params): array
    {
        $url = $this->makeFullUrl($api);
        $params = $this->attachCredential($params);

        $resp = $this->getRequest()->get($url, $params);
        $this->response = $resp;

        return $this->handleResponse($resp);
    }

    /**
     * @throws PrivateApiException
     */
    protected function handleResponse(Response $response): array
    {
        if($response->json("errcode") !== 0){
            throw new PrivateApiException("请求失败，response：".$response->body(), $response->status());
        }
        return $response->json();
    }
}