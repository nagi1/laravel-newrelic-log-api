<?php

namespace Nagi\LaravelNewrelicLogApi;

class LaravelNewrelicLogApi
{
    /**
     * Mutate the context array before sending it to the API.
     *
     * @var callable
     */
    public static $mutateContextUsing;

    public static function mutateContextUsing(callable $callback)
    {
        static::$mutateContextUsing = $callback;
    }

    public static function logHandler(): string
    {
        return config('newrelic-log-api.log_handler');
    }
}
