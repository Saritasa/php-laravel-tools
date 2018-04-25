<?php

namespace Saritasa\LaravelTools\DTO\PhpClasses;

use Saritasa\LaravelTools\Enums\PhpDocPropertyAccessTypes;

/**
 * PHPDoc class property details. Stores information about class property, declared in PhpDoc class' section.
 */
class ClassPhpDocPropertyObject extends VariableObject
{
    const ACCESS_TYPE = 'accessType';

    /**
     * Property access type.
     *
     * @see PhpDocPropertyAccessTypes for available values
     * @var string
     */
    public $accessType;
}
