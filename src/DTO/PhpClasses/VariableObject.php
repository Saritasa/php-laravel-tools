<?php

namespace Saritasa\LaravelTools\DTO\PhpClasses;

use Saritasa\Dto;

/**
 * Php variable details. Property, type, description and nullable sign properties.
 */
class VariableObject extends Dto
{
    const NAME = 'name';
    const TYPE = 'type';
    const NULLABLE = 'nullable';
    const DESCRIPTION = 'description';

    /**
     * Variable name.
     *
     * @var string
     */
    public $name;

    /**
     * Variable type.
     *
     * @var string
     */
    public $type;

    /**
     * Is Variable can be null.
     *
     * @var boolean
     */
    public $nullable;

    /**
     * Variable description.
     *
     * @var string
     */
    public $description;
}
