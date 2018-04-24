<?php

namespace Saritasa\LaravelTools\DTO\PhpClasses;

use Saritasa\Dto;

/**
 * Class details that are used to build class declaration.
 */
class ClassObject extends Dto
{
    const NAME = 'name';
    const NAMESPACE = 'namespace';
    const PARENT = 'parent';
    const DESCRIPTION = 'description';
    const PROPERTIES = 'properties';
    const CONSTANTS = 'constants';
    const METHODS = 'methods';

    /**
     * Class name.
     *
     * @var string
     */
    public $name;

    /**
     * Class namespace.
     *
     * @var string
     */
    public $namespace;

    /**
     * Class parent.
     *
     * @var string
     */
    public $parent;

    /**
     * Class description.
     *
     * @var string
     */
    public $description;

    /**
     * Class properties.
     *
     * @var ClassPropertyObject[]
     */
    public $properties = [];

    /**
     * Class constants.
     *
     * @var ClassConstantObject[]
     */
    public $constants;

    /**
     * Class methods.
     *
     * @var FunctionObject[]
     */
    public $methods = [];
}
