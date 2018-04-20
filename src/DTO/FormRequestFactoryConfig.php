<?php

namespace Saritasa\LaravelTools\DTO;

/**
 * Configuration for form request generation. Contains necessary class names and namespaces.
 */
class FormRequestFactoryConfig extends ModelBasedClassFactoryConfig
{
    const SUGGEST_ATTRIBUTE_NAMES_CONSTANTS = 'suggestAttributeNamesConstants';

    /**
     * Suggest that model contain constants with attribute names (like const FIRST_NAME = 'first_name').
     *
     * @var bool
     */
    public $suggestAttributeNamesConstants = true;
}
