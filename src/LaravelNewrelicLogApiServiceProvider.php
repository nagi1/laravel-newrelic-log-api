<?php

namespace Nagi\LaravelNewrelicLogApi;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Nagi\LaravelNewrelicLogApi\Commands\LaravelNewrelicLogApiCommand;

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
