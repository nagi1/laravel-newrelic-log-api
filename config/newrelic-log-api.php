<?php

return [
    'enabled' => env('NEWRELIC_ENABLED', false),

    /**
     * New Relic API key or License key
     *
     * @see https://docs.newrelic.com/docs/logs/log-api/introduction-log-api/#setup
     */
    'api_key' => env('NEWRELIC_API_KEY'),

    /**
     * The base URL for the new relic log API
     *
     * @see https://docs.newrelic.com/docs/logs/log-api/introduction-log-api/#endpoint
     */
    'base_url' => env('NEWRELIC_BASE_URL', 'https://log-api.eu.newrelic.com'),

    /**
     * The minimum logging level at which this handler will be triggered
     */
    'level' => env('NEWRELIC_LEVEL', 'debug'),

    /**
     * Retry sending the log to New Relic API
     */
    'retry' => env('NEWRELIC_RETRY', 3),

    /**
     * Delay between retries in milliseconds
     */
    'retry_delay' => env('NEWRELIC_RETRY_DELAY', 1000),

    /**
     * Queue name to use for sending logs to New Relic API
     */
    'queue' => env('NEWRELIC_QUEUE', config('queue.default')),

    /**
     * Log handler to use for sending logs to New Relic API
     */
    'log_handler' => \Nagi\LaravelNewrelicLogApi\NewrelicLogHandler::class,
];
