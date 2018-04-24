<?php

namespace Saritasa\LaravelTools\Swagger;

use Saritasa\LaravelTools\DTO\Routes\ApiRouteObject;
use Saritasa\LaravelTools\Enums\HttpMethods;
use Throwable;
use WakeOnWeb\Component\Swagger\Loader\JsonLoader;
use WakeOnWeb\Component\Swagger\Loader\YamlLoader;
use WakeOnWeb\Component\Swagger\Specification\PathItem;
use WakeOnWeb\Component\Swagger\Specification\Swagger;
use WakeOnWeb\Component\Swagger\SwaggerFactory;

/**
 * Swagger specification file reader. Allows to retrieve API specification.
 */
class SwaggerReader
{
    private static $specificationsCache = [];

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
     * @param string $sourceFile Path to file with swagger specification
     *
     * @return Swagger
     */
    public function getSpecification(string $sourceFile): Swagger
    {
        if (!isset(static::$specificationsCache[$sourceFile])) {
            static::$specificationsCache[$sourceFile] = $this->swaggerFactory->buildFrom($sourceFile);
        }

        return static::$specificationsCache[$sourceFile];
    }

    /**
     * Parse swagger path specification.
     *
     * @param PathItem $path Path to parse
     * @param string $url Url that is used for passed endpoints paths
     * @param array $supportedSecuritySchemes $this->parsePathSpecification($path, $url, $supportedSecuritySchemes)
     *
     * @return ApiRouteObject[]
     */
    protected function parsePathSpecification(PathItem $path, string $url, array $supportedSecuritySchemes): array
    {
        $apiRouteObjects = [];
        foreach (HttpMethods::getConstants() as $method) {
            $operation = $path->getOperationFor($method);

            if (!$operation) {
                continue;
            }

            /**
             * Due to swagger library restrictions we can't get all security schemes, only can iterate
             * and check is schema exists or catch "Undefined offset" error.
             */
            $securitySchemes = [];
            $securityRequirements = $operation->getSecurity();
            foreach ($securityRequirements as $requirement) {
                foreach ($supportedSecuritySchemes as $scheme) {
                    try {
                        $requirement->getScheme($scheme);
                    } catch (Throwable $e) {
                        continue;
                    }

                    $securitySchemes[] = $scheme;
                }
            }

            $routesGroup = $operation->getTags()[0] ?? null;

            $apiRouteObjects[] = new ApiRouteObject([
                ApiRouteObject::GROUP => $routesGroup,
                ApiRouteObject::METHOD => $method,
                ApiRouteObject::URL => $url,
                // Now only one security scheme per route supported
                ApiRouteObject::SECURITY_SCHEME => $securitySchemes[0] ?? null,
                ApiRouteObject::DESCRIPTION => $operation->getSummary() ?? $operation->getDescription(),
            ]);
        }

        return $apiRouteObjects;
    }

    /**
     * Get api specifications from swagger source.
     *
     * @param string $sourceFile Path to file with swagger specification
     * @param string[] $supportedSecuritySchemes Security schemes that are supported, for example AuthToken
     *
     * @return ApiRouteObject[]
     */
    public function getApiPaths(string $sourceFile, array $supportedSecuritySchemes): array
    {
        $swagger = $this->getSpecification($sourceFile);
        $paths = $swagger->getPaths()->getPaths();
        $apiRouteObjects = [];
        foreach ($paths as $url => $path) {
            $pathRouteObjects = $this->parsePathSpecification($path, $url, $supportedSecuritySchemes);
            $apiRouteObjects = array_merge($apiRouteObjects, $pathRouteObjects);
        }

        return $apiRouteObjects;
    }
}
