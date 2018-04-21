<?php

namespace Saritasa\LaravelTools\Swagger;

use WakeOnWeb\Component\Swagger\Loader\JsonLoader;
use WakeOnWeb\Component\Swagger\Loader\YamlLoader;
use WakeOnWeb\Component\Swagger\Specification\Swagger;
use WakeOnWeb\Component\Swagger\SwaggerFactory;

/**
 * Swagger specification file reader. Allows to retrieve API specification.
 */
class SwaggerReader
{
    /**
     * Swagger file processor.
     *
     * @var SwaggerFactory
     */
    private $swaggerFactory;

    /**
     * Swagger specification file reader. Allows to retrieve API specification.
     *
     * @param SwaggerFactory $swaggerFactory Swagger file processor
     * @param YamlLoader $yamlLoader YAML-files loader
     * @param JsonLoader $jsonLoader JSON-files loader
     */
    public function __construct(SwaggerFactory $swaggerFactory, YamlLoader $yamlLoader, JsonLoader $jsonLoader)
    {
        $this->swaggerFactory = $swaggerFactory;
        $this->swaggerFactory->addLoader($yamlLoader);
        $this->swaggerFactory->addLoader($jsonLoader);
    }

    /**
     * Returns swagger file specification.
     *
     * @param string $path Path where
     *
     * @return Swagger
     */
    public function getSpecification(string $path): Swagger
    {
        return $this->swaggerFactory->buildFrom($path);
    }
}
