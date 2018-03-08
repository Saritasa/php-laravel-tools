<?php

namespace Saritasa\LaravelTools\DTO;

use Saritasa\LaravelTools\Enums\PhpDocPropertyAccessTypes;
use Saritasa\Transformers\DtoModel;

/**
 * Class property DTO. Stores property details.
 */
class ClassPropertyObject extends DtoModel
{
    const NAME = 'name';
    const TYPE = 'type';
    const NULLABLE = 'nullable';
    const DESCRIPTION = 'description';
    const ACCESS_TYPE = 'accessType';

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
     * Property description
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
}
