<?php

use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use Nagi\LaravelNewrelicLogApi\Client;
use Nagi\LaravelNewrelicLogApi\Events\NewrelicLogApiResponseEvent;
use Nagi\LaravelNewrelicLogApi\Jobs\LogToNewrelicJob;
use Nagi\LaravelNewrelicLogApi\LaravelNewrelicLogApi;

beforeEach(function () {
    // add newrelic-log-api driver to the config
    $channels = config('logging.channels');
    $channels['newrelic-log-api'] = [
        'driver' => 'monolog',
        'handler' => LaravelNewrelicLogApi::logHandler(),
        'level' => 'debug',
    ];
    config(['logging.channels' => $channels]);

    // enable the newrelic-log-api
    config(['newrelic-log-api.enabled' => true]);

    // make sure default log channel is set to newrelic-log-api
    config(['logging.default' => 'newrelic-log-api']);

    $this->partialMock(Client::class, function (MockInterface $mock) {
        $mock->shouldReceive('send')->andReturn(new Response(new Psr7Response(200, [], '')));
    });

    // make sure the queue is sync
    config(['queue.default' => 'sync']);
});

afterEach(function () {
    LaravelNewrelicLogApi::$mutateContextUsing = null;
    $this->forgetMock(Client::class);
    $this->forgetMock(ExceptionHandler::class);
});

it('logs a message through the client', function () {
    $this->mock(Client::class, function (MockInterface $mock) {
        $mock->shouldReceive('isEnabled')
            ->andReturn(true);

        $mock->shouldReceive('send')
            ->once()
            ->with('Log message', ['key' => 'value']);
    });

    app(LogToNewrelicJob::class, [
        'message' => 'Log message',
        'context' => ['key' => 'value'],
    ])->handle();
});

it('dispatches the job to the configured queue', function () {
    Queue::fake([
        LogToNewrelicJob::class,
    ]);

    config(['newrelic-log-api.queue' => 'newrelic']);

    logger()->info('Log message', ['key' => 'value']);

    Queue::assertPushed(LogToNewrelicJob::class, function (LogToNewrelicJob $job) {
        return $job->queue === 'newrelic';
    });
});

it('fires Event with response status code and json body after sending the log', function () {
    Event::fake([
        NewrelicLogApiResponseEvent::class,
    ]);

    $this->partialMock(Client::class, function (MockInterface $mock) {

        $mock->shouldReceive('send')
            ->once()
            ->with('Log message', ['key' => 'value'])
            ->andReturn((new Response(new Psr7Response(200, [], '{"requestId": "d2e4a4c1-0001-be87-19e5-0191d0c81658"}'))));
    });

    app(LogToNewrelicJob::class, [
        'message' => 'Log message',
        'context' => ['key' => 'value'],
    ])->handle();

    Event::assertDispatched(NewrelicLogApiResponseEvent::class, function (NewrelicLogApiResponseEvent $event) {
        return $event->statusCode === 200 && $event->jsonBody === ['requestId' => 'd2e4a4c1-0001-be87-19e5-0191d0c81658'];
    });
});

it('does not throw an exception if an error occurs while sending the log and report it', function () {
    $this->partialMock(Client::class, function (MockInterface $mock) {
        $mock->shouldReceive('send')
            ->once()
            ->with('Log message', ['key' => 'value'])
            ->andThrow(new Exception('Error sending log to New Relic API'));
    });

    $this->partialMock(ExceptionHandler::class, function (MockInterface $mock) {
        $mock->shouldReceive('report')
            ->once();
    });

    app(LogToNewrelicJob::class, [
        'message' => 'Log message',
        'context' => ['key' => 'value'],
    ])->handle();
});

it('sends correct structure to newrelic', function () {
    $this->forgetMock(Client::class);

    Queue::fake([
        LogToNewrelicJob::class,
    ]);

    Http::fake([
        '*' => Http::response('', 200),
    ]);

    logger()->info('Log message', [
        'key' => 'value',
        'nested' => [
            'key' => 'value',
        ],
    ]);

    Queue::assertPushed(LogToNewrelicJob::class, function (LogToNewrelicJob $job) {
        $checkContext = true;

        collect($job->context)->each(function ($value, $key) use (&$checkContext) {
            $keys = ['key', 'nested', 'env', 'ip_request', 'url', 'hostname', 'level'];

            if (! in_array($key, $keys)) {
                $checkContext = false;
            }

            if ($key === 'nested') {
                $checkContext = $value == ['key' => 'value'];
            }
        });

        return $job->message === 'Log message' && $checkContext;
    });

    // dispatch the job
    (new LogToNewrelicJob('Log message', [
        'level' => 'INFO',
        'key' => 'value',
        'nested' => [
            'key' => 'value',
        ],
        'env' => 'testing',
        'ip_request' => '127.0.0.1',
        'url' => 'http://localhost',
        'hostname' => 'localhost',
    ],
    ))->handle();

    Http::assertSent(function (Request $request) {
        $data = json_decode($request->body(), true);

        // This attribute will be unset in the handler
        unset($data['attributes']['level']);

        expect($data)->toEqual([
            'timestamp' => now()->toIso8601String(),
            'attributes' => [
                'key' => 'value',
                'nested' => [
                    'key' => 'value',
                ],
                'env' => 'testing',
                'ip_request' => '127.0.0.1',
                'url' => 'http://localhost',
                'hostname' => 'localhost',
            ],
            'level' => 'INFO',
            'message' => 'Log message',
        ]);

        return true;
    });
});

it('will user defined context mutator', function () {
    $this->forgetMock(Client::class);

    Queue::fake([
        LogToNewrelicJob::class,
    ]);

    LaravelNewrelicLogApi::mutateContextUsing(function ($context) {
        $context['custom'] = 'value';

        return $context;
    });

    Http::fake([
        '*' => Http::response('', 200),
    ]);

    logger()->info('Log message', [
        'key' => 'value',
    ]);

    Queue::assertPushed(LogToNewrelicJob::class, function (LogToNewrelicJob $job) {
        return $job->message === 'Log message' && $job->context['custom'] === 'value';
    });

    // dispatch the job
    (new LogToNewrelicJob('Log message', [
        'level' => 'INFO',
        'key' => 'value',
        'custom' => 'value',
    ],
    ))->handle();

    Http::assertSent(function (Request $request) {
        $data = json_decode($request->body(), true);

        // This attribute will be unset in the handler
        unset($data['attributes']['level']);

        expect($data)->toEqual([
            'timestamp' => now()->toIso8601String(),
            'attributes' => [
                'key' => 'value',
                'custom' => 'value',
            ],
            'level' => 'INFO',
            'message' => 'Log message',
        ]);

        return true;
    });
});

it('it will replace reserved logs attributes with a prefix', function () {
    Queue::fake([
        LogToNewrelicJob::class,
    ]);

    logger()->info('Log message', [
        'message' => 'value',
        'log' => 'value',
        'LOG' => 'value',
        'MESSAGE' => 'value',
        'msg' => 'value',
        'level' => 'INFO',
    ]);

    Queue::assertPushed(LogToNewrelicJob::class, function (LogToNewrelicJob $job) {
        $contextCheck = true;

        collect($job->context)->each(function ($value, $key) use (&$contextCheck) {
            $keys = ['attr_message', 'attr_log', 'attr_LOG', 'attr_MESSAGE', 'attr_msg', 'attr_level', 'level'];

            if (! in_array($key, $keys)) {
                $contextCheck = false;
            }

            if ($key === 'attr_level') {
                $contextCheck = $value === 'INFO';
            }

            if (in_array($key, ['attr_message', 'attr_log', 'attr_LOG', 'attr_MESSAGE', 'attr_msg'])) {
                $contextCheck = $value === 'value';
            }
        });

        return $job->message === 'Log message' && $contextCheck;
    });
});

it('will pickup the correct log value', function () {
    Queue::fake([
        LogToNewrelicJob::class,
    ]);

    logger()->warning('Log message');

    Queue::assertPushed(LogToNewrelicJob::class, function (LogToNewrelicJob $job) {
        return $job->context['level'] === 'WARNING';
    });
});
