<?php

namespace Oh86\JYH\Utils;

class PrivateApiUtil
{
    private string $app;
    private string $ticket;

    /**
     * @param string $app
     * @param string $ticket
     */
    public function __construct(string $app, string $ticket)
    {
        $this->app = $app;
        $this->ticket = $ticket;
    }

    public function genToken(int $time): string
    {
        return md5(sprintf("%s%d%s", $this->app, $time, $this->ticket));
    }

    /**
     * @param int $time
     * @return array{app: string, time: int, token: string}
     */
    public function genCredential(int $time): array
    {
        return [
            "app" => $this->app,
            "time" => $time,
            "token" => $this->genToken($time),
        ];
    }
}