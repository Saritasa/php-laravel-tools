<?php

namespace Saritasa\LaravelTools\DTO;

use Saritasa\Dto;

/**
 * Configuration for form request generation. Contains necessary class names and namespaces.
 */
class FormRequestFactoryConfig extends Dto
{
    const NAMESPACE = 'namespace';
    const PARENT_CLASS_NAME = 'parentClassName';
    const CLASS_NAME = 'className';
    const MODEL_CLASS_NAME = 'modelClassName';
    const RESULT_FILENAME = 'resultFilename';
    const TEMPLATE_FILENAME = 'templateFilename';
    const EXCLUDED_ATTRIBUTES = 'excludedAttributes';
    const SUGGEST_ATTRIBUTE_NAMES_CONSTANTS = 'suggestAttributeNamesConstants';

    /**
     * Namespace of new form request.
     *
     * @var string
     */
    public $namespace;

    /**
     * Fully-qualified parent class name that new form request should extend.
     *
     * @var string
     */
    public $parentClassName;

    /**
     * New form request class name.
     *
     * @var string
     */
    public $className;

    /**
     * Fully-qualified model class name for which need to generate form request.
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
     * Form Request Template full path name.
     *
     * @var string
     */
    public $templateFilename;

    /**
     * Model attributes names that should be ignored by factory builder.
     *
     * @var array
     */
    public $excludedAttributes = [];

    /**
     * Suggest that model contain constants with attribute names (like const FIRST_NAME = 'first_name').
     *
     * @var bool
     */
    public $suggestAttributeNamesConstants = true;
}
