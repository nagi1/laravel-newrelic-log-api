# New Relic Log API for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nagi/laravel-newrelic-log-api.svg?style=flat-square)](https://packagist.org/packages/nagi/laravel-newrelic-log-api)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/nagi1/laravel-newrelic-log-api/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/nagi1/laravel-newrelic-log-api/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/nagi1/laravel-newrelic-log-api/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/nagi1/laravel-newrelic-log-api/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/nagi/laravel-newrelic-log-api.svg?style=flat-square)](https://packagist.org/packages/nagi/laravel-newrelic-log-api)

Integrate New Relic Log API with Laravel your laravel application.

```php
    // in loggin.php
    'stack' => [
                'driver' => 'stack',
                'channels' => ['single', 'newrelic-log-api'],
                'ignore_exceptions' => false,
            ],

        //...
        'newrelic-log-api' => [
            'driver' => 'monolog',
            'handler' => \Nagi\LaravelNewrelicLogApi\LaravelNewrelicLogApi::logHandler(),
            'level' => 'debug',
        ],
```

```php
logger('newrelic-log-api')->info('Hey Mom!');

// or if you are using stack

logger()->info('Hey Mom!');
```

## Support Me

Does your business depend on my contributions? Reach out and support me on [PayPal](https://www.paypal.com/paypalme/nagix1). All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## Installation

You can install the package via composer:

```bash
composer require nagi/laravel-newrelic-log-api
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-newrelic-log-api-config"
```

This is the contents of the published config file:

```php
return [
    'enabled' => env('NEWRELIC_ENABLED', false),

    /**
     * New Relic API key or License key
     *
     * @see https://docs.newrelic.com/docs/logs/log-api/introduction-log-api/#setup
     */
    'api_key' => env('NEWRELIC_API_KEY'),

    /**
     * The base URL for the new relic log API
     *
     * @see https://docs.newrelic.com/docs/logs/log-api/introduction-log-api/#endpoint
     */
    'base_url' => env('NEWRELIC_BASE_URL', 'https://log-api.eu.newrelic.com'),

    /**
     * The minimum logging level at which this handler will be triggered
     */
    'level' => env('NEWRELIC_LEVEL', 'debug'),

    /**
     * Retry sending the log to New Relic API
     */
    'retry' => env('NEWRELIC_RETRY', 3),

    /**
     * Delay between retries in milliseconds
     */
    'retry_delay' => env('NEWRELIC_RETRY_DELAY', 1000),

    /**
     * Queue name to use for sending logs to New Relic API
     */
    'queue' => env('NEWRELIC_QUEUE', env('QUEUE_CONNECTION')),

    /**
     * Log handler to use for sending logs to New Relic API
     */
    'log_handler' => \Nagi\LaravelNewrelicLogApi\NewrelicLogHandler::class,
];

```

## Usage

### Get your newrelic License/API

https://one.newrelic.com/api-keys

### Add env values in your .env

```
NEWRELIC_ENABLED=true
NEWRELIC_API_KEY=<your_key>

# if your account is not EU change the baseurl
# https://log-api.newrelic.com/log/v1
NEWRELIC_BASE_URL=https://log-api.eu.newrelic.com

```

### Add new relic channel to your config

In your `logging.php` add `new-relic-log-api` channel

```php
        'newrelic-log-api' => [
            'driver' => 'monolog',
            'handler' => \Nagi\LaravelNewrelicLogApi\LaravelNewrelicLogApi::logHandler(),
            'level' => env('NEWRELIC_LEVEL', 'debug')
        ],

```

### Add it to your logging stack

in `logging.php`

```php
    'stack' => [
                'driver' => 'stack',
                'channels' => ['single', 'newrelic-log-api'],
                'ignore_exceptions' => false,
            ],
```

## Listen to the event

If you are intrested in geting the response from the New Relic API you can listen to the event `NewrelicLogApiResponseEvent` It will have the status and the response from the API.

```php
    protected $listen = [
        NewrelicLogApiResponseEvent::class => [
            NewrelicLogApiResponseListener::class,
        ],
    ];
```

##

## Note on context log (attributes)

When sending one of the following attributes in the context it will be prefixed with `attr_` to avoid overriding the whole message.

See: [New Relic Log API](https://docs.newrelic.com/docs/logs/log-api/introduction-log-api/#attributes)

## Extending

All of the classes in this package are loaded via Laravel's service container meaning you can easily replace them with your own implementation. On Your Own Risk.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [Ahmed Nagi](https://github.com/nagi1)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
