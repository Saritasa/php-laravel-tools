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
Allows to generate FormRequest class with rules based on model's attributes.
There are two generated sets:
+ Properties docblock with type, name and description (from column comment)
+ Form request rules

#### Example

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

And one more example:

```php
<?php

namespace App\Http\Requests;

/**
* JobRequest form request.
*
* @property-read integer $manager_id Property manager ID who created job
* @property-read string $title Job title
* @property-read text|null $description Note for job
* @property-read boolean|null $is_urgent Filag set if job is urgent
* @property-read integer $status_id Job status ID.
* @property-read integer $category_id Job service ID
* @property-read integer $property_id Property ID (address)
* @property-read string|null $unit Unit #
* @property-read datetime $proposed_start_date Proposed job start  date
* @property-read datetime $estimate_review_date Proposed job enddate
* @property-read datetime|null $start_date real start_date
* @property-read datetime|null $completed_at real end_date
* @property-read decimal|null $price real price
* @property-read integer|null $accepted_bid_id 
* @property-read integer|null $contractor_id 
* @property-read float|null $rating Rating. Sets when job Approved, 
* @property-read integer $created_by 
* @property-read integer|null $updated_by 
* @property-read boolean $reopens_count
*/
class JobRequest extends \Illuminate\Foundation\Http\FormRequest
{
    /**
    * Rules that should be applied to validate request.
    *
    * @return array
    */
    public function rules(): array
    {
        return [
            'manager_id' => 'required|integer',
            'title' => 'required|string',
            'description' => 'nullable|text',
            'is_urgent' => 'nullable|boolean',
            'status_id' => 'required|integer',
            'category_id' => 'required|integer',
            'property_id' => 'required|integer',
            'unit' => 'nullable|string',
            'proposed_start_date' => 'required|datetime',
            'estimate_review_date' => 'required|datetime',
            'start_date' => 'nullable|datetime',
            'completed_at' => 'nullable|datetime',
            'price' => 'nullable|decimal',
            'accepted_bid_id' => 'nullable|integer',
            'contractor_id' => 'nullable|integer',
            'rating' => 'nullable|float',
            'created_by' => 'required|integer',
            'updated_by' => 'nullable|integer',
            'reopens_count' => 'required|boolean'
        ];
    }
}

```

#### What's next?
For now it's just basic information and not working form request but it will be improved soon :)

What need to improve:
1. Type mapping for properties in doc-block (now there doctrine-type name are placed instead of PHP-types)
2. Extend available rules dictionary (and extract interface to have ability add laravel-fluent-validation rules dictionary)
3. There is parameter in config that requests to use model constants instead of strings attributes names in request
4. Declare only necessary packages in composer.json
5. Unit tests of course :)

## Contributing
See [CONTRIBUTING](CONTRIBUTING.md) and [Code of Conduct](CONDUCT.md),
if you want to make contribution (pull request)
or just build and test project on your own.

## Resources

* [Changes History](CHANGES.md)
* [Bug Tracker](https://github.com/Saritasa/php-laravel-tools/issues)
* [Authors](https://github.com/Saritasa/php-laravel-tools/contributors)
