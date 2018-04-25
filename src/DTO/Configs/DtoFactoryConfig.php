<?php

namespace Saritasa\LaravelTools\DTO\Configs;

/**
 * Configuration for DTO generation. Contains necessary class names and namespaces.
 */
class DtoFactoryConfig extends ModelBasedClassFactoryConfig
{
    const IMMUTABLE = 'immutable';
    const STRICT_TYPES = 'strictTypes';
    const WITH_CONSTANTS = 'withConstants';

    /**
     * Is generated DTO should be immutable.
     *
     * @var bool
     */
    public $immutable;

    /**
     * Is DTO should control getters and setters attribute types.
     *
     * @var bool
     */
    public $strictTypes;

    /**
     * Whether constants block with attributes names should be generated.
     *
     * @var bool
     */
    public $withConstants;
}
