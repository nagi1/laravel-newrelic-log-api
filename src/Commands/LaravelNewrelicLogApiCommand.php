<?php

namespace Nagi\LaravelNewrelicLogApi\Commands;

use Illuminate\Console\Command;

class LaravelNewrelicLogApiCommand extends Command
{
    public $signature = 'laravel-newrelic-log-api';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
