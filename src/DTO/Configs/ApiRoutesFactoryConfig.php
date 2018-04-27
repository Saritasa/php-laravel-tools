<?php

namespace Saritasa\LaravelTools\DTO\Configs;

/**
 * Configuration for form request generation. Contains necessary class names and namespaces.
 */
class ApiRoutesFactoryConfig extends TemplateBasedFactoryConfig
{
    const CONTROLLERS_NAMESPACE = 'controllersNamespace';
    const SWAGGER_FILE = 'swaggerFile';
    const SECURITY_SCHEMES_MIDDLEWARES = 'securitySchemesMiddlewares';
    const ROOT_GROUP_MIDDLEWARES = 'rootGroupMiddlewares';

    /**
     * API controllers namespace.
     *
     * @var string|null
     */
    public $controllersNamespace;

    /**
     * Swagger file with routes declarations.
     *
     * @var string|null
     */
    public $swaggerFile;

    /**
     * List of middlewares that should be applied to handle route security scheme.
     *
     * @var array|null
     */
    public $securitySchemesMiddlewares;

    /**
     * list of middlewares that should be applied for root routes group.
     *
     * @var array
     */
    public $rootGroupMiddlewares;
}
