<?php

namespace Saritasa\LaravelTools\DTO\PhpClasses;

/**
 * {@inheritdoc}
 * Represents function parameter details.
 */
class MethodParameterObject extends VariableObject
{
    const DEFAULT = 'default';

    /**
     * Default value for method parameter.
     *
     * @var mixed
     */
    public $default;
}
