<?php

namespace Nagi\LaravelNewrelicLogApi\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Nagi\LaravelNewrelicLogApi\Client;
use Nagi\LaravelNewrelicLogApi\Events\NewrelicLogApiResponseEvent;

class LogToNewrelicJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $message, public array $context = [])
    {
        if ($queue = config('newrelic-log-api.queue')) {
            $this->onQueue($queue);
        }
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $response = app(Client::class)->send($this->message, $this->context);

            event(
                app(NewrelicLogApiResponseEvent::class, [
                    'statusCode' => $response->status(),
                    'jsonBody' => $response->json(),
                ])
            );

        } catch (\Throwable $e) {
            report($e);

            $this->fail($e);

            return;
        }
    }
}
