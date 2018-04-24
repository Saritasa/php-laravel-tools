<?php

namespace Saritasa\LaravelTools\DTO\PhpClasses;

use Saritasa\Dto;
use Saritasa\LaravelTools\Enums\ClassMemberVisibilityTypes;

/**
 * Function details that are used to build function declaration.
 */
class FunctionObject extends Dto
{
    const NAME = 'name';
    const RETURN_TYPE = 'returnType';
    const DESCRIPTION = 'description';
    const PARAMETERS = 'parameters';
    const VISIBILITY_TYPE = 'visibilityType';

    /**
     * Function name.
     *
     * @var string
     */
    public $name;

    /**
     * Function return type.
     *
     * @var string
     */
    public $returnType;

    /**
     * Function description.
     *
     * @var string
     */
    public $description;

    /**
     * List of Function parameters.
     *
     * @var array Key-value pairs where key is parameter name and value is parameter type
     */
    public $parameters = [];

    /**
     * Function visibility type. Private, protected or public.
     *
     * @see ClassMemberVisibilityTypes for available values
     * @var string
     */
    public $visibilityType;
}
