<?php

namespace Saritasa\LaravelTools\DTO;

/**
 * Configuration for class based on model generation. Contains necessary class names and namespaces.
 */
class ModelBasedClassFactoryConfig extends ClassFactoryConfig
{
    const MODEL_CLASS_NAME = 'modelClassName';
    const EXCLUDED_ATTRIBUTES = 'excludedAttributes';

    /**
     * Fully-qualified model class name for which need to generate new class.
     *
     * @var string
     */
    public $modelClassName;

    /**
     * Model attributes names that should be ignored by factory builder.
     *
     * @var array
     */
    public $excludedAttributes = [];
}
