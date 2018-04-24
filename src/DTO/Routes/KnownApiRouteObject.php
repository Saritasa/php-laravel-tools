<?php

namespace Saritasa\LaravelTools\DTO\Routes;

use Saritasa\Dto;

/**
 * Known route parameters details such as controller, controller's method and route name.
 */
class KnownApiRouteObject extends Dto
{
    const CONTROLLER = 'controller';
    const METHOD = 'method';
    const NAME = 'name';

    /**
     * Known controller name for route.
     *
     * @var string|null
     */
    public $controller;

    /**
     * Known method name for route.
     *
     * @var string|null
     */
    public $method;

    /**
     * Known route name for route.
     *
     * @var string|null
     */
    public $name;
}
