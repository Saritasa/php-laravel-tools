<?php

namespace Saritasa\LaravelTools\DTO\PhpClasses;

use Saritasa\Dto;
use Saritasa\LaravelTools\Enums\ClassMemberVisibilityTypes;

/**
 * Class constant object. Stores constant declaration details.
 */
class ClassConstantObject extends Dto
{
    const NAME = 'name';
    const VALUE = 'value';
    const DESCRIPTION = 'description';
    const VISIBILITY_TYPE = 'visibilityType';

    /**
     * Constant name.
     *
     * @var string
     */
    public $name;

    /**
     * Constant value.
     *
     * @var string
     */
    public $value;

    /**
     * Constant description.
     *
     * @var string
     */
    public $description;

    /**
     * Constant visibility type. Private, protected or public.
     *
     * @see ClassMemberVisibilityTypes for available values
     * @var string
     */
    public $visibilityType;
}
