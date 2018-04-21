<?php

namespace Saritasa\LaravelTools\DTO;

use Saritasa\Dto;
use Saritasa\LaravelTools\Enums\HttpMethods;

/**
 * API route parameters details.
 */
class ApiRouteObject extends Dto
{
    const GROUP = 'group';
    const METHOD = 'method';
    const URL = 'url';
    const SECURITY_SCHEME = 'securityScheme';
    const DESCRIPTION = 'description';

    /**
     * To which group this route belongs.
     *
     * @var string
     */
    public $group;

    /**
     * Endpoint method.
     *
     * @see HttpMethods::getConstants() for valid values
     * @var string
     */
    public $method;

    /**
     * Endpoint path.
     *
     * @var string
     */
    public $url;

    /**
     * Security scheme token or null when route is insecure.
     *
     * @var string|null
     */
    public $securityScheme;

    /**
     * Endpoint description.
     *
     * @var string
     */
    public $description;
}
