<?php

namespace Saritasa\LaravelTools\DTO\Configs;

/**
 * Configuration for class generation. Contains necessary class names and namespaces.
 */
abstract class ClassFactoryConfig extends TemplateBasedFactoryConfig
{
    const NAMESPACE = 'namespace';
    const PARENT_CLASS_NAME = 'parentClassName';
    const CLASS_NAME = 'className';

    /**
     * Namespace of new class.
     *
     * @var string
     */
    public $namespace;

    /**
     * Fully-qualified parent class name that new class should extend.
     *
     * @var string
     */
    public $parentClassName;

    /**
     * New class name.
     *
     * @var string
     */
    public $className;
}
