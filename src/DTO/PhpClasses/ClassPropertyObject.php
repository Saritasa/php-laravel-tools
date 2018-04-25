<?php

namespace Saritasa\LaravelTools\DTO\PhpClasses;

use Saritasa\LaravelTools\Enums\ClassMemberVisibilityTypes;

/**
 * Class property DTO. Stores property details.
 */
class ClassPropertyObject extends VariableObject
{
    const VISIBILITY_TYPE = 'visibilityType';

    /**
     * Property visibility type. Public, protected or private.
     *
     * @see ClassMemberVisibilityTypes for available values
     * @var string
     */
    public $visibilityType;
}
