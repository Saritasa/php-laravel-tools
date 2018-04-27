<?php

namespace Saritasa\LaravelTools\Factories;

use Exception;
use Illuminate\Support\Collection;
use Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\ApiRoutesGenerator;
use Saritasa\LaravelTools\CodeGenerators\CodeFormatter;
use Saritasa\LaravelTools\CodeGenerators\CommentsGenerator;
use Saritasa\LaravelTools\DTO\Configs\ApiRoutesFactoryConfig;
use Saritasa\LaravelTools\DTO\Routes\ApiRouteObject;
use Saritasa\LaravelTools\Services\TemplateWriter;
use Saritasa\LaravelTools\Swagger\SwaggerReader;

/**
 * Api routes factory. Allows to build api routes definition according to swagger specification.
 */
class ApiRoutesDeclarationFactory extends TemplateBasedFactory
{
    // Template placeholders
    private const PLACEHOLDER_CONTROLLERS_NAMESPACE = 'controllersNamespace';
    private const PLACEHOLDER_API_ROUTES_DEFINITIONS = 'apiRoutesDefinitions';

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
     * Php comments generator. Allows to comment lines and blocks of text.
     *
     * @var CommentsGenerator
     */
    private $commentsGenerator;

    /**
     * Api routes generation methods facade. Allows to render route, routes block and routes group definition.
     *
     * @var ApiRoutesGenerator
     */
    private $apiRoutesGenerator;

    /**
     * Api routes factory. Allows to build api routes definition according to swagger specification.
     *
     * @param TemplateWriter $templateWriter Templates files writer
     * @param CodeFormatter $codeFormatter Code style utility. Allows to format code according to settings
     * @param CommentsGenerator $commentsGenerator Php comments generator. Allows to comment lines and blocks of text
     * @param SwaggerReader $swaggerReader Swagger specification file reader
     * @param ApiRoutesGenerator $apiRoutesGenerator Api routes generation methods facade. Allows to render route,
     *     routes block and routes group definition
     */
    public function __construct(
        TemplateWriter $templateWriter,
        CodeFormatter $codeFormatter,
        CommentsGenerator $commentsGenerator,
        SwaggerReader $swaggerReader,
        ApiRoutesGenerator $apiRoutesGenerator
    ) {
        parent::__construct($templateWriter, $codeFormatter);
        $this->swaggerReader = $swaggerReader;
        $this->commentsGenerator = $commentsGenerator;
        $this->apiRoutesGenerator = $apiRoutesGenerator;
    }

    /**
     * Returns api routes definitions lines.
     *
     * @return string
     */
    private function getRoutesDefinition(): string
    {
        $apiRoutes = $this->swaggerReader->getApiPaths(
            $this->config->swaggerFile,
            array_keys($this->config->securitySchemesMiddlewares)
        );

        $result = [];
        // Separate routes by security schemes
        $routesBySecurityScheme = Collection::make($apiRoutes)->groupBy(ApiRouteObject::SECURITY_SCHEME)->toArray();
        foreach ($routesBySecurityScheme as $securityScheme => $schemeRoutes) {
            $schemeRoutesDefinitions = [];
            // Separate routes inside security schemes by groups
            $groupsInsideScheme = Collection::make($schemeRoutes)->groupBy(ApiRouteObject::GROUP)->toArray();
            foreach ($groupsInsideScheme as $group => $groupRoutes) {
                $groupDescription = $group ?
                    $this->codeFormatter->toSentence($this->codeFormatter->anyCaseToWords($group) . ' routes')
                    : null;
                if ($schemeRoutesDefinitions) {
                    $schemeRoutesDefinitions[] = '';
                }
                $schemeRoutesDefinitions[] = $this->apiRoutesGenerator->renderBlock($groupRoutes, $groupDescription);
            }
            $schemeRoutesDefinitionsBlock = $this->codeFormatter->linesToBlock($schemeRoutesDefinitions);

            if ($result) {
                $result[] = '';
            }

            // Use appropriate middleware to handle security scheme
            $groupSecurityMiddleware = $this->config->securitySchemesMiddlewares[$securityScheme] ?? null;

            // If group of routes are secure, than need to wrap them into routes group with middleware
            if ($groupSecurityMiddleware) {
                $humanReadableToken = $this->codeFormatter->anyCaseToWords($securityScheme);
                $routeGroupDescription = "Routes under {$humanReadableToken} security";
                $result[] = $this->apiRoutesGenerator->renderGroup(
                    $schemeRoutesDefinitionsBlock,
                    [$groupSecurityMiddleware],
                    $routeGroupDescription
                );
            } else {
                $result[] = $schemeRoutesDefinitionsBlock;
            }
        }

        $allRoutesDefinition = $this->codeFormatter->linesToBlock($result);

        return $this->apiRoutesGenerator->renderGroup(
            $allRoutesDefinition,
            $this->config->rootGroupMiddlewares
        );
    }

    /**
     * Returns template's placeholders values.
     *
     * @return array
     * @throws Exception
     */
    protected function getPlaceHoldersValues(): array
    {
        $routesDefinitions = $this->codeFormatter->indentBlock($this->getRoutesDefinition());

        return [
            static::PLACEHOLDER_CONTROLLERS_NAMESPACE => $this->config->controllersNamespace,
            static::PLACEHOLDER_API_ROUTES_DEFINITIONS => $routesDefinitions,
        ];
    }
}
