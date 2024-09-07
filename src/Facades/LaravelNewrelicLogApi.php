<?php

namespace Nagi\LaravelNewrelicLogApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Nagi\LaravelNewrelicLogApi\LaravelNewrelicLogApi
 */
class LaravelNewrelicLogApi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Nagi\LaravelNewrelicLogApi\LaravelNewrelicLogApi::class;
    }
}
