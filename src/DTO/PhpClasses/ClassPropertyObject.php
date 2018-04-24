<?php

namespace Saritasa\LaravelTools\DTO\PhpClasses;

use Saritasa\Dto;
use Saritasa\LaravelTools\Enums\ClassMemberVisibilityTypes;
use Saritasa\LaravelTools\Enums\PhpDocPropertyAccessTypes;

/**
 * Class property DTO. Stores property details.
 */
class ClassPropertyObject extends Dto
{
    const NAME = 'name';
    const TYPE = 'type';
    const NULLABLE = 'nullable';
    const DESCRIPTION = 'description';
    const ACCESS_TYPE = 'accessType';
    const VISIBILITY_TYPE = 'visibilityType';

    /**
     * Property name.
     *
     * @var string
     */
    public $name;

    /**
     * Property type.
     *
     * @var string
     */
    public $type;

    /**
     * Is property can be null.
     *
     * @var boolean
     */
    public $nullable;

    /**
     * Property description.
     *
     * @var string
     */
    public $description;

    /**
     * Property access type.
     *
     * @see PhpDocPropertyAccessTypes for available values
     * @var string
     */
    public $accessType;

    /**
     * Property visibility type. Public, protected or private.
     *
     * @see ClassMemberVisibilityTypes for available values
     * @var string
     */
    public $visibilityType;
}