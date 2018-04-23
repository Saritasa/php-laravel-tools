<?php

namespace Saritasa\LaravelTools\Factories;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\ApiRouteGenerator;
use Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\ApiRoutesGroupGenerator;
use Saritasa\LaravelTools\CodeGenerators\CodeFormatter;
use Saritasa\LaravelTools\DTO\ApiRouteObject;
use Saritasa\LaravelTools\DTO\ApiRoutesFactoryConfig;
use Saritasa\LaravelTools\Enums\HttpMethods;
use Saritasa\LaravelTools\Services\TemplateWriter;
use Saritasa\LaravelTools\Swagger\SwaggerReader;
use Throwable;
use WakeOnWeb\Component\Swagger\Specification\PathItem;

/**
 * Api routes factory. Allows to build api routes definition according to swagger specification.
 */
class ApiRoutesDeclarationFactory extends TemplateBasedFactory
{
    private const PLACEHOLDER_CONTROLLERS_NAMESPACE = 'controllersNamespace';
    private const PLACEHOLDER_API_ROUTES_DEFINITIONS = 'apiRoutesDefinitions';

    /**
     * API route generator, allows to build route declaration.
     *
     * @var ApiRouteGenerator
     */
    private $apiRouteGenerator;

    /**
     * API routes group declaration. Allows to wrap block of routes into group.
     *
     * @var ApiRoutesGroupGenerator
     */
    private $apiRoutesGroupGenerator;

    /**
     * Route factory configuration.
     *
     * @var ApiRoutesFactoryConfig
     */
    protected $config;

    /**
     * Swagger specification file reader.
     *
     * @var SwaggerReader
     */
    private $swaggerReader;

    /**
     * Api routes factory. Allows to build api routes definition according to swagger specification.
     *
     * @param TemplateWriter $templateWriter Templates files writer
     * @param CodeFormatter $codeFormatter Code style utility. Allows to format code according to settings
     * @param SwaggerReader $swaggerReader Swagger specification file reader
     * @param ApiRouteGenerator $apiRouteGenerator Api route generator. Allows to build route declaration with
     *     description according to route details
     * @param ApiRoutesGroupGenerator $apiRoutesGroupGenerator Api routes group generator. Allows to generate api
     *     routes group declaration
     */
    public function __construct(
        TemplateWriter $templateWriter,
        CodeFormatter $codeFormatter,
        SwaggerReader $swaggerReader,
        ApiRouteGenerator $apiRouteGenerator,
        ApiRoutesGroupGenerator $apiRoutesGroupGenerator
    ) {
        parent::__construct($templateWriter, $codeFormatter);
        $this->apiRouteGenerator = $apiRouteGenerator;
        $this->apiRoutesGroupGenerator = $apiRoutesGroupGenerator;
        $this->swaggerReader = $swaggerReader;
    }

    /**
     * Parse swagger path specification.
     *
     * @param PathItem $path Path to parse
     * @param string $url
     *
     * @return array
     */
    protected function parsePathSpecification(PathItem $path, string $url): array
    {
        $apiRouteObjects = [];
        foreach (HttpMethods::getConstants() as $method) {
            $operation = $path->getOperationFor($method);

            if (!$operation) {
                continue;
            }

            $securitySchemes = [];
            $securityRequirements = $operation->getSecurity();
            /**
             * Due to swagger library restrictions we can't get all security schemes, only can iterate
             * and check is schema exists or catch "Undefined offset" error.
             */
            foreach ($securityRequirements as $requirement) {
                foreach ($this->config->securitySchemesMiddlewares as $scheme => $middleware) {
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
     * Returns api routes definitions lines.
     *
     * @return array
     */
    private function getRoutesDefinition(): array
    {
        $swagger = $this->swaggerReader->getSpecification($this->config->swaggerFile);

        $apiRouteObjects = [];
        foreach ($swagger->getPaths()->getPaths() as $url => $path) {
            $apiRouteObjects = array_merge($apiRouteObjects, $this->parsePathSpecification($path, $url));
        }

        $routeDefinitions = [];
        $routesBySecurityScheme = Collection::make($apiRouteObjects)->groupBy(ApiRouteObject::SECURITY_SCHEME);
        foreach ($routesBySecurityScheme as $securityScheme => $schemeRoutes) {
            $schemeRoutesDefinitions = [];
            $routesByGroup = Collection::make($schemeRoutes)->groupBy(ApiRouteObject::GROUP);
            foreach ($routesByGroup as $group => $groupRoutes) {
                if ($group) {
                    $groupDescription = str_replace('_', ' ', ucfirst(strtolower(Str::snake($group)))) . ' routes.';
                    $groupDescriptionDelimiter = str_repeat('/', strlen($groupDescription) + 6);
                    $schemeRoutesDefinitions[] =
                        "$groupDescriptionDelimiter\n// {$groupDescription} //\n$groupDescriptionDelimiter\n";
                }
                foreach ($groupRoutes as $route) {
                    $schemeRoutesDefinitions[] = $this->apiRouteGenerator->render($route);
                }
            }

            $schemeRoutesDefinitionsBlock = implode("\n", $schemeRoutesDefinitions);

            $groupMiddleware = $this->config->securitySchemesMiddlewares[$securityScheme] ?? null;
            if (!$groupMiddleware) {
                $routeDefinitions[] = $schemeRoutesDefinitionsBlock;
            } else {
                $routeDefinitions[] = '';
                $humanReadableToken = str_replace('_', ' ', ucfirst(strtolower(Str::snake($securityScheme))));
                $securityRoutesDescription = "Routes under {$humanReadableToken} security";
                $routeDefinitions[] = $this->apiRoutesGroupGenerator
                    ->render($schemeRoutesDefinitionsBlock, [$groupMiddleware], $securityRoutesDescription);
            }
        }

        return $routeDefinitions;
    }

    /**
     * Returns template's placeholders values.
     *
     * @return array
     * @throws Exception
     */
    protected function getPlaceHoldersValues(): array
    {
        $routesDefinitions = $this->getRoutesDefinition();

        return [
            static::PLACEHOLDER_CONTROLLERS_NAMESPACE => $this->config->controllersNamespace,
            static::PLACEHOLDER_API_ROUTES_DEFINITIONS => $this->codeFormatter
                ->indentBlock(implode("\n", $routesDefinitions)),
        ];
    }
}
