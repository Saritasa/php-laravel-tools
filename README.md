# Laravel Tools

[![PHP Unit](https://github.com/Saritasa/php-laravel-tools/workflows/PHP%20Unit/badge.svg)](https://github.com/Saritasa/php-laravel-tools/actions)
[![PHP CodeSniffer](https://github.com/Saritasa/php-laravel-tools/workflows/PHP%20Codesniffer/badge.svg)](https://github.com/Saritasa/php-laravel-tools/actions)
[![CodeCov](https://codecov.io/gh/Saritasa/php-laravel-tools/branch/master/graph/badge.svg)](https://codecov.io/gh/Saritasa/php-laravel-tools)
[![Release](https://img.shields.io/github/release/Saritasa/php-laravel-tools.svg)](https://github.com/Saritasa/php-laravel-tools/releases)
[![PHPv](https://img.shields.io/packagist/php-v/saritasa/laravel-tools.svg)](http://www.php.net)
[![Downloads](https://img.shields.io/packagist/dt/saritasa/laravel-tools.svg)](https://packagist.org/packages/saritasa/laravel-tools)

This package was designed to help developers scaffold parts of code for Laravel-based projects.

## Installation and configuration

Install the ```saritasa/laravel-tools``` package as dev dependency:

```bash
$ composer require saritasa/laravel-tools --dev
```

If you use Laravel 5.4 or less,
or 5.5+ with [package discovery](https://laravel.com/docs/5.5/packages#package-discovery) disabled,
add the LaravelToolsServiceProvider in ``AppServiceProvider.php``:

```php
    public function register()
    {
        if ($this->app->environment() === 'local') {
            // If we are in local environment, enable some developer's tools
            ...
            $this->app->register(LaravelToolsServiceProvider::class);
            ...
        }
    }
```

Publish config with

```bash
$ artisan vendor:publish --tag=laravel_tools
```

## Available artisan commands

### artisan make:form_request ModelName FormRequestName
Allows to generate FormRequest class with rules based on model's attributes.

### artisan make:dto ModelName DtoName
Allows to generate DTO class with properties based on model's attributes.

### artisan make:api_routes
Allows to build API routes declaration based on swagger specification.

### artisan make:api_controllers
Allows to scaffold API Controllers with actions based on swagger specification.

## Documentation
Please, read our [**WIKI**](https://github.com/Saritasa/php-laravel-tools/wiki) for complete documentation.

## Known issues
+ [Enum DB type is casted as String via custom doctrine mapping](https://github.com/Saritasa/php-laravel-tools/issues/3)
+ [Tinyint type is casted by Doctrine as Boolean](https://github.com/Saritasa/php-laravel-tools/issues/4)

## What's next?
What need to improve:
1. Declare only necessary packages in composer.json instead of entire laravel

## Contributing
See [CONTRIBUTING](CONTRIBUTING.md) and [Code of Conduct](CONDUCT.md),
if you want to make contribution (pull request)
or just build and test project on your own.

## Resources

* [Changes History](CHANGES.md)
* [Bug Tracker](https://github.com/Saritasa/php-laravel-tools/issues)
* [Authors](https://github.com/Saritasa/php-laravel-tools/contributors)
