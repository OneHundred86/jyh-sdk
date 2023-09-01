<?php

namespace Oh86\JYH\Facades;

use Illuminate\Support\Facades\Facade;
use Oh86\JYH\OAuth\AbstractOAuthService;

/**
 * @see AbstractOAuthService;
 */
class JYHOAuth extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AbstractOAuthService::class;
    }
}