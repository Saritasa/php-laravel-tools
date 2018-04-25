<?php

namespace Saritasa\LaravelTools\Enums;

use Saritasa\Enum;

/**
 * Available PhpDoc scalar types.
 */
class PhpDocScalarTypes extends Enum
{
    const BOOLEAN = 'boolean';
    const INTEGER = 'integer';
    const FLOAT = 'float';
    const STRING = 'string';
    const VOID = 'void';
    const MIXED = 'mixed';
}
