<?php

namespace Nagi\LaravelNewrelicLogApi\Tests;

use Nagi\LaravelNewrelicLogApi\LaravelNewrelicLogApiServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
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
    }
}
