<?php

namespace Saritasa\LaravelTools\DTO\Configs;

/**
 * Configuration for api controller generation. Contains necessary class names and namespaces.
 */
class ApiControllerFactoryConfig extends ClassFactoryConfig
{
    const NAMES_SUFFIX = 'namesSuffix';

    /**
     * Api Controllers name suffix.
     *
     * @var string
     */
    public $namesSuffix;
}
