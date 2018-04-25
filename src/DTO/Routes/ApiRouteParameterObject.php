<?php

namespace Saritasa\LaravelTools\DTO\Routes;

use Saritasa\Dto;

/**
 * API route parameter details.
 */
class ApiRouteParameterObject extends Dto
{
    const NAME = 'name';
    const IN = 'in';
    const DESCRIPTION = 'description';
    const REQUIRED = 'required';
    const TYPE = 'type';

    /**
     * Route parameter name.
     *
     * @var string
     */
    public $name;

    /**
     * Route parameter location. Path or body.
     *
     * @var string
     */
    public $in;

    /**
     * Route parameter description.
     *
     * @var string
     */
    public $description;

    /**
     * Whether route parameter required or not.
     *
     * @var boolean
     */
    public $required;

    /**
     * Route parameter type.
     *
     * @var string
     */
    public $type;
}
