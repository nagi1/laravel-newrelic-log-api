<?php

namespace Nagi\LaravelNewrelicLogApi;

use Nagi\LaravelNewrelicLogApi\Commands\LaravelNewrelicLogApiCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelNewrelicLogApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-newrelic-log-api')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_newrelic_log_api_table')
            ->hasCommand(LaravelNewrelicLogApiCommand::class);
    }
}
