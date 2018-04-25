<?php

namespace Saritasa\LaravelTools\DTO\Routes;

use Saritasa\Dto;

/**
 * Known route parameters details such as controller, controller's method and route name.
 */
class KnownApiRouteObject extends Dto
{
    const CONTROLLER = 'controller';
    const ACTION = 'action';
    const NAME = 'name';

    /**
     * Known controller name for route.
     *
     * @var string|null
     */
    public $controller;

    /**
     * Known controller's method name for route.
     *
     * @var string|null
     */
    public $action;

    /**
     * Known route name for route.
     *
     * @var string|null
     */
    public $name;
}
