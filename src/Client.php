<?php

namespace Nagi\LaravelNewrelicLogApi;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class Client
{
    protected string $baseUrl;

    protected PendingRequest $http;

    public bool $enabled;

    public function __construct()
    {
        $this->http = Http::withHeaders(['Api-Key' => config('newrelic-log-api.api_key')])
            ->acceptJson()
            ->baseUrl(config('newrelic-log-api.base_url'));
    }

    /**
     * Log only if enabled and in production/staging environments.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return config('newrelic-log-api.enabled', false);
    }

    /**
     * @return \GuzzleHttp\Promise\PromiseInterface|Response
     */
    public function send(string $message, array $context = [])
    {
        return $this->http
            ->retry(config('newrelic-log-api.retry') ?? 3, config('newrelic-log-api.retry_delay') ?? 1000)
            ->post('/log/v1', [
                'timestamp' => now()->toIso8601String(),
                'attributes' => array_merge($context, [
                    'entity.name' => config('newrelic-log-api.entity_name'),
                ]),
                'level' => $context['level'],
                'message' => $message,
            ]);
    }
}
