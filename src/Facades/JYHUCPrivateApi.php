<?php

namespace Oh86\JYH\Facades;

use Illuminate\Support\Facades\Facade;
use Oh86\JYH\PrivateApi\UCPrivateApi;

/**
 * @see UCPrivateApi
 */
class JYHUCPrivateApi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return UCPrivateApi::class;
    }
}