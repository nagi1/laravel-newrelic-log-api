<?php

namespace Nagi\LaravelNewrelicLogApi;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Nagi\LaravelNewrelicLogApi\Jobs\LogToNewrelicJob;

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

        $context = collect($this->mutateContext($record))->filter(function ($value, $key) {
            $isClosure = ! is_string($value) && is_callable($value);

            return ! is_object($value) && ! $isClosure;
        })->toArray();

        app(LogToNewrelicJob::class, [
            'message' => $record->message,
            'context' => $context,
        ])->dispatch($record->message, $context);
    }

    public function mutateContext(LogRecord $logRecord): array
    {
        $context = $logRecord->context;

        if (isset(LaravelNewrelicLogApi::$mutateContextUsing)) {
            return (array) call_user_func(LaravelNewrelicLogApi::$mutateContextUsing, $context);
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

        // To not override user's log attribute
        if (isset($context['level'])) {
            $context['attr_level'] = $context['level'];
            unset($context['level']);
        }

        $context['level'] = $logRecord->level->getName();

        return $context;
    }
}
