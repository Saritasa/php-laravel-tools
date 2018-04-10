<?php

namespace Saritasa\LaravelTools\DTO;

use Saritasa\Dto;

/**
 * Configuration for DTO generation. Contains necessary class names and namespaces.
 */
class DtoFactoryConfig extends Dto
{
    const NAMESPACE = 'namespace';
    const PARENT_CLASS_NAME = 'parentClassName';
    const CLASS_NAME = 'className';
    const MODEL_CLASS_NAME = 'modelClassName';
    const RESULT_FILENAME = 'resultFilename';
    const TEMPLATE_FILENAME = 'templateFilename';
    const EXCLUDED_ATTRIBUTES = 'excludedAttributes';
    const IMMUTABLE = 'immutable';
    const STRICT_TYPES = 'strictTypes';
    const WITH_CONSTANTS = 'withConstants';

    /**
     * Namespace of new DTO.
     *
     * @var string
     */
    public $namespace;

    /**
     * Fully-qualified parent class name that new DTO should extend.
     *
     * @var string
     */
    public $parentClassName;

    /**
     * New DTO class name.
     *
     * @var string
     */
    public $className;

    /**
     * Fully-qualified model class name for which need to generate DTO.
     *
     * @var string
     */
    public $modelClassName;

    /**
     * Result file full name.
     *
     * @var
     */
    public $resultFilename;

    /**
     * DTO Template full path name.
     *
     * @var string
     */
    public $templateFilename;

    /**
     * Model attributes names that should be ignored by factory builder.
     *
     * @var array
     */
    public $excludedAttributes;

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
