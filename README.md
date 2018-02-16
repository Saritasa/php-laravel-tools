# Laravel Tools

[![Build Status](https://travis-ci.org/Saritasa/php-laravel-tools.svg?branch=master)](https://travis-ci.org/Saritasa/php-laravel-tools)
[![CodeCov](https://codecov.io/gh/Saritasa/php-laravel-tools/branch/master/graph/badge.svg)](https://codecov.io/gh/Saritasa/php-laravel-tools)
[![Release](https://img.shields.io/github/release/Saritasa/php-laravel-tools.svg)](https://github.com/Saritasa/php-laravel-tools/releases)
[![PHPv](https://img.shields.io/packagist/php-v/saritasa/laravel-tools.svg)](http://www.php.net)
[![Downloads](https://img.shields.io/packagist/dt/saritasa/laravel-tools.svg)](https://packagist.org/packages/saritasa/laravel-tools)

This package was designed to help developers scaffold parts of code for Laravel-based projects.

## Usage

Install the ```saritasa/laravel-tools``` package:

```bash
$ composer require saritasa/laravel-tools
```

## Available artisan commands
### artisan make:form_request ModelName
Allows to generate FormRequest class with rules based on model's attributes:

```php
/**
* UserRequest form request.
*
* @property-read string $first_name 
* @property-read string|null $last_name 
* @property-read string $email 
* @property-read string|null $password 
* @property-read integer|null $company_id 
* @property-read string|null $phone 
* @property-read string|null $avatar_url 
* @property-read float|null $approval_price Price for pre-approval
* @property-read boolean|null $role_id 
* @property-read integer|null $contractor_id 
* @property-read string|null $remember_token 
* @property-read boolean|null $is_temporary_password
*/
class UserRequest extends \Illuminate\Foundation\Http\FormRequest
{
    /**
    * Rules that should be applied to validate request.
    *
    * @return array
    */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string',
            'last_name' => 'nullable|string',
            'email' => 'required|string',
            'password' => 'nullable|string',
            'company_id' => 'nullable|integer',
            'phone' => 'nullable|string',
            'avatar_url' => 'nullable|string',
            'approval_price' => 'nullable|float',
            'role_id' => 'nullable|boolean',
            'contractor_id' => 'nullable|integer',
            'remember_token' => 'nullable|string',
            'is_temporary_password' => 'nullable|boolean'
        ];
    }
}
```

For now it's just basic information and not working form request but it will be improved soon :)

## Contributing
See [CONTRIBUTING](CONTRIBUTING.md) and [Code of Conduct](CONDUCT.md),
if you want to make contribution (pull request)
or just build and test project on your own.

## Resources

* [Changes History](CHANGES.md)
* [Bug Tracker](https://github.com/Saritasa/php-laravel-tools/issues)
* [Authors](https://github.com/Saritasa/php-laravel-tools/contributors)
