<?php

namespace Saritasa\LaravelTools\DTO\Routes;

use Saritasa\LaravelTools\DTO\PhpClasses\FunctionObject;

/**
 * Api route implementation details.
 */
class ApiRouteImplementationObject extends KnownApiRouteObject
{
    const FUNCTION = 'function';

    /**
     * Guessed function that handles api method details.
     *
     * @var FunctionObject
     */
    public $function;
}
