<?php

namespace Nagi\LaravelNewrelicLogApi\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Nagi\LaravelNewrelicLogApi\LaravelNewrelicLogApiServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Nagi\\LaravelNewrelicLogApi\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelNewrelicLogApiServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-newrelic-log-api_table.php.stub';
        $migration->up();
        */
    }
}
