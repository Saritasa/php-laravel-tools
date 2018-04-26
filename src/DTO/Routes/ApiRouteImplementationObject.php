<?php

namespace Saritasa\LaravelTools\DTO\Routes;

use Saritasa\LaravelTools\DTO\PhpClasses\FunctionObject;

/**
 * Api route implementation details.
 */
class ApiRouteImplementationObject extends KnownApiRouteObject
{
    const FUNCTION = 'function';
    const RESOURCE_CLASS = 'resourceClass';

    /**
     * Guessed function that handles api method details.
     *
     * @var FunctionObject
     */
    public $function;

    /**
     * Guessed resource class name, handled by this route.
     *
     * @var string|null
     */
    public $resourceClass;
}
