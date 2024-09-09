<?php

namespace Nagi\LaravelNewrelicLogApi;

use Nagi\LaravelNewrelicLogApi\Commands\LaravelNewrelicLogApiCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelNewrelicLogApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-newrelic-log-api')
            ->hasConfigFile();
    }

    public function packageRegistered()
    {
        $this->app->singleton('newrelic-log-api', function () {
            return new Client;
        });

        $this->app->alias('newrelic-log-api', Client::class);
    }
}
