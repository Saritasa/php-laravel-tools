<?php

namespace Saritasa\LaravelTools\DTO;

use Saritasa\Dto;

/**
 * Configuration for class generation. Contains necessary class names and namespaces.
 */
abstract class ClassFactoryConfig extends Dto
{
    const NAMESPACE = 'namespace';
    const PARENT_CLASS_NAME = 'parentClassName';
    const CLASS_NAME = 'className';
    const RESULT_FILENAME = 'resultFilename';
    const TEMPLATE_FILENAME = 'templateFilename';

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

    /**
     * Result file full name.
     *
     * @var
     */
    public $resultFilename;

    /**
     * Class template full path name.
     *
     * @var string
     */
    public $templateFilename;
}
