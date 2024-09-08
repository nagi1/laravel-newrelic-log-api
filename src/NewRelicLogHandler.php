<?php

namespace Nagi\LaravelNewrelicLogApi;

use App\Jobs\LogToNewrelicJob;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class NewrelicLogHandler extends AbstractProcessingHandler
{
    public function __construct(int|string|Level $level = Level::Debug, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        if (! app(Client::class)->isEnabled()) {
            return;
        }

        $context = collect($this->mutateContext($record->context))->filter(function ($value, $key) {
            return ! is_object($value) && ! is_callable($value);
        })->toArray();

        app(LogToNewrelicJob::class)::dispatch($record->message, $context);
    }

    public function mutateContext(array $context): array
    {
        if (isset(LaravelNewrelicLogApi::$mutateContextUsing)) {
            return call_user_func(LaravelNewrelicLogApi::$mutateContextUsing, $context);
        }

        $context['env'] = app()->environment();
        $context['ip_request'] = request()->ip();
        $context['url'] = request()->fullUrl();
        $context['hostname'] = gethostname();

        $overwriteMessageAttributes = [
            'message',
            'log',
            'LOG',
            'MESSAGE',
            'msg',
        ];

        // To not override the original message attribute
        foreach ($overwriteMessageAttributes as $attribute) {
            if (isset($context[$attribute])) {
                $context[sprintf('attr_%s', $attribute)] = $context[$attribute];
                unset($context[$attribute]);
            }
        }

        return $context;
    }
}
