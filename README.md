# Laravel Tools

[![Build Status](https://travis-ci.org/Saritasa/php-laravel-tools.svg?branch=master)](https://travis-ci.org/Saritasa/php-laravel-tools)
[![CodeCov](https://codecov.io/gh/Saritasa/php-laravel-tools/branch/master/graph/badge.svg)](https://codecov.io/gh/Saritasa/php-laravel-tools)
[![Release](https://img.shields.io/github/release/Saritasa/php-laravel-tools.svg)](https://github.com/Saritasa/php-laravel-tools/releases)
[![PHPv](https://img.shields.io/packagist/php-v/saritasa/laravel-tools.svg)](http://www.php.net)
[![Downloads](https://img.shields.io/packagist/dt/saritasa/laravel-tools.svg)](https://packagist.org/packages/saritasa/laravel-tools)

This package was designed to help developers scaffold parts of code for Laravel-based projects.

## Usage

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

## Available artisan commands
### artisan make:form_request ModelName
Allows to generate FormRequest class with rules based on model's attributes.
There are two generated sets:
+ Properties docblock with type, name and description (from column comment)
+ Form request rules

## Form request builder
Allows to build form request for model create or update request
### Form request attributes names
Attributes names format is configurable via `models.suggest_attribute_names_constants` config
and can be formatted as follows:
+ Simple string: **'role_id'**
+ Model attribute constant name: **User::ROLE_ID**

### Validation rules dictionaries
There are two validation rule dictionary that can be configured in `rules.dictionary` config:
+ **StringValidationRulesDictionary** that builds string rules: 'required|integer'
+ **FluentValidationRulesDictionary** that builds object rules: Rule::required()->int()

### Example
'String' example, where attributes names and rules is a just string:
```php
<?php

namespace App\Http\Requests;

/**
* BidRequest form request.
*
* @property-read integer $user_id User who created bid
* @property-read integer $contractor_id Contractor ID
* @property-read integer $job_id Job ID
* @property-read integer|null $status_id
* @property-read integer|null $accepted_by Manager who accepted bid
* @property-read integer|null $pre_approved_by
* @property-read string $proposed_start_date Estimated Start date
* @property-read integer $days_count Estimated  End date
* @property-read string $proposed_end_date Estimated Start date
* @property-read float $proposed_cost Estimated cost
* @property-read string|null $note
* @property-read string|null $proposal_attachment Attached proposal
* @property-read string|null $proposal_uploaded_at Proposal uploaded date
* @property-read string $url_token
*/
class BidRequest extends \Illuminate\Foundation\Http\FormRequest
{
    /**
    * Rules that should be applied to validate request.
    *
    * @return array
    */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id|integer',
            'contractor_id' => 'required|exists:contractors,id|integer',
            'job_id' => 'required|exists:jobs,id|integer',
            'status_id' => 'nullable|exists:bid_statuses,id|integer',
            'accepted_by' => 'nullable|exists:users,id|integer',
            'pre_approved_by' => 'nullable|exists:users,id|integer',
            'proposed_start_date' => 'required|date',
            'days_count' => 'required|integer',
            'proposed_end_date' => 'required|date',
            'proposed_cost' => 'required|numeric',
            'note' => 'nullable|string|max:65535',
            'proposal_attachment' => 'nullable|string|max:255',
            'proposal_uploaded_at' => 'nullable|date',
            'url_token' => 'required|string|max:191'
        ];
    }
}

```

OOP example, where attribute names is a model constants and rules is fluent validation object:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Bid;
use Saritasa\Laravel\Validation\Rule;

/**
* BidRequest form request.
*
* @property-read integer $user_id User who created bid
* @property-read integer $contractor_id Contractor ID
* @property-read integer $job_id Job ID
* @property-read integer|null $status_id
* @property-read integer|null $accepted_by Manager who accepted bid
* @property-read integer|null $pre_approved_by
* @property-read string $proposed_start_date Estimated Start date
* @property-read integer $days_count Estimated  End date
* @property-read string $proposed_end_date Estimated Start date
* @property-read float $proposed_cost Estimated cost
* @property-read string|null $note
* @property-read string|null $proposal_attachment Attached proposal
* @property-read string|null $proposal_uploaded_at Proposal uploaded date
* @property-read string $url_token
*/
class BidRequest extends FormRequest
{
    /**
    * Rules that should be applied to validate request.
    *
    * @return array
    */
    public function rules(): array
    {
        return [
            Bid::USER_ID => Rule::required()->exists('users','id')->int(),
            Bid::CONTRACTOR_ID => Rule::required()->exists('contractors','id')->int(),
            Bid::JOB_ID => Rule::required()->exists('jobs','id')->int(),
            Bid::STATUS_ID => Rule::nullable()->exists('bid_statuses','id')->int(),
            Bid::ACCEPTED_BY => Rule::nullable()->exists('users','id')->int(),
            Bid::PRE_APPROVED_BY => Rule::nullable()->exists('users','id')->int(),
            Bid::PROPOSED_START_DATE => Rule::required()->date(),
            Bid::DAYS_COUNT => Rule::required()->int(),
            Bid::PROPOSED_END_DATE => Rule::required()->date(),
            Bid::PROPOSED_COST => Rule::required()->numeric(),
            Bid::NOTE => Rule::nullable()->string()->max(65535),
            Bid::PROPOSAL_ATTACHMENT => Rule::nullable()->string()->max(255),
            Bid::PROPOSAL_UPLOADED_AT => Rule::nullable()->date(),
            Bid::URL_TOKEN => Rule::required()->string()->max(191)
        ];
    }
}

```

## Known issues
+ Enum DB type is casted as String via custom doctrine mapping
+ Tinyint type is casted by Doctrine as Boolean

## What's next?
What need to improve:
1. Declare only necessary packages in composer.json instead of entire laravel
2. Unit tests of course :)
3. Generation of DTO for model and return this DTO from form request

## Contributing
See [CONTRIBUTING](CONTRIBUTING.md) and [Code of Conduct](CONDUCT.md),
if you want to make contribution (pull request)
or just build and test project on your own.

## Resources

* [Changes History](CHANGES.md)
* [Bug Tracker](https://github.com/Saritasa/php-laravel-tools/issues)
* [Authors](https://github.com/Saritasa/php-laravel-tools/contributors)
