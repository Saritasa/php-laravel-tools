<?php

namespace Saritasa\LaravelTools\Enums;

use Saritasa\Enum;

/**
 * Available Php mixed types.
 */
class PhpMixedTypes extends Enum
{
    const ARRAY = 'array';
    const OBJECT = 'object';
    const CALLABLE = 'callable';
    const ITERABLE = 'iterable';
}
