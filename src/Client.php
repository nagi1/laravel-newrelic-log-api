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
        $this->enabled = config('newrelic-log-api.enabled', false);
        $this->baseUrl = config('newrelic-log-api.base_url');

        $this->http = Http::withHeaders(['Api-Key' => config('newrelic-log-api.api_key')])
            ->acceptJson()
            ->baseUrl($this->baseUrl);
    }

    /**
     * Log only if enabled and in production/staging environments.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
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
                'attributes' => $context,
                'message' => $message,
            ]);
    }
}
