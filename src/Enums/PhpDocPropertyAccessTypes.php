<?php

namespace Saritasa\LaravelTools\Enums;

use Saritasa\Enum;

/**
 * Available PhpDoc properties access type.
 */
class PhpDocPropertyAccessTypes extends Enum
{
    const READ = 'read';
    const WRITE = 'write';
    const READ_AND_WRITE = 'read_and_write';
}
