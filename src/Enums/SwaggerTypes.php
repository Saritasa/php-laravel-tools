<?php

namespace Saritasa\LaravelTools\Enums;

use Saritasa\Enum;

/**
 * Available swagger scalar types.
 */
class SwaggerTypes extends Enum
{
    const STRING = 'string';
    const NUMBER = 'number';
    const INTEGER = 'integer';
    const BOOLEAN = 'boolean';
    const ARRAY = 'array';
    const OBJECT = 'object';
}
