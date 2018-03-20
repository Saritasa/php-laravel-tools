<?php

namespace Saritasa\LaravelTools\Enums;

use Saritasa\Enum;

/**
 * Available laravel validation rules types.
 */
class LaravelValidationTypes extends Enum
{
    const ARRAY = 'array';
    const BOOLEAN = 'boolean';
    const DATE = 'date';
    const INTEGER = 'integer';
    const STRING = 'string';
    const NUMERIC = 'numeric';
}
