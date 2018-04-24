<?php

namespace Saritasa\LaravelTools\Factories;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\ApiRoutesBlockGenerator;
use Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\ApiRoutesGroupGenerator;
use Saritasa\LaravelTools\CodeGenerators\CodeFormatter;
use Saritasa\LaravelTools\CodeGenerators\CommentsGenerator;
use Saritasa\LaravelTools\DTO\ApiRoutesFactoryConfig;
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
     * Php comments generator. Allows to comment lines and blocks of text.
     *
     * @var CommentsGenerator
     */
    private $commentsGenerator;

    /**
     * Api routes block generator. Allows to build routes block (not routes group) with description.
     *
     * @var ApiRoutesBlockGenerator
     */
    private $apiRoutesBlockGenerator;

    /**
     * Api routes factory. Allows to build api routes definition according to swagger specification.
     *
     * @param TemplateWriter $templateWriter Templates files writer
     * @param CodeFormatter $codeFormatter Code style utility. Allows to format code according to settings
     * @param CommentsGenerator $commentsGenerator Php comments generator. Allows to comment lines and blocks of text
     * @param SwaggerReader $swaggerReader Swagger specification file reader
     * @param ApiRoutesGroupGenerator $apiRoutesGroupGenerator Api routes group generator. Allows to generate api
     *     routes group declaration
     * @param ApiRoutesBlockGenerator $apiRoutesBlockGenerator Api routes block generator. Allows to build routes block
     *     (not routes group) with description
     */
    public function __construct(
        TemplateWriter $templateWriter,
        CodeFormatter $codeFormatter,
        CommentsGenerator $commentsGenerator,
        SwaggerReader $swaggerReader,
        ApiRoutesGroupGenerator $apiRoutesGroupGenerator,
        ApiRoutesBlockGenerator $apiRoutesBlockGenerator
    ) {
        parent::__construct($templateWriter, $codeFormatter);
        $this->apiRoutesGroupGenerator = $apiRoutesGroupGenerator;
        $this->apiRoutesBlockGenerator = $apiRoutesBlockGenerator;
        $this->swaggerReader = $swaggerReader;
        $this->commentsGenerator = $commentsGenerator;
    }

    /**
     * Separates camel-cased string token by words.
     *
     * @param string $text String token to separate
     * @param bool $capitalizedFirstWord Whether first letter of sentence should be capitalized or not
     *
     * @return string
     */
    private function camelCaseToSentence(string $text, bool $capitalizedFirstWord = true): string
    {
        $sentence = str_replace('_', ' ', Str::snake($text));

        if ($capitalizedFirstWord) {
            $sentence = ucfirst($sentence);
        }

        return $sentence;
    }

    /**
     * Returns api routes definitions lines.
     *
     * @return array
     */
    private function getRoutesDefinition(): array
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
                $groupDescription = $group ? $this->camelCaseToSentence($group) . ' routes.' : null;
                if ($schemeRoutesDefinitions) {
                    $schemeRoutesDefinitions[] = '';
                }
                $schemeRoutesDefinitions[] = $this->apiRoutesBlockGenerator->render($groupRoutes, $groupDescription);
            }
            // Use appropriate middleware to handle security scheme
            $groupMiddleware = $this->config->securitySchemesMiddlewares[$securityScheme] ?? null;

            $schemeRoutesDefinitionsBlock = $this->codeFormatter->linesToBlock($schemeRoutesDefinitions);

            if ($result) {
                $result[] = '';
            }

            // If group of routes are secure, than need to wrap them into routes group with middleware
            if ($groupMiddleware) {
                $humanReadableToken = $this->camelCaseToSentence($securityScheme);
                $securityRoutesDescription = "Routes under {$humanReadableToken} security";
                $result[] = $this->apiRoutesGroupGenerator->render(
                    $schemeRoutesDefinitionsBlock,
                    [$groupMiddleware],
                    $securityRoutesDescription
                );
            } else {
                $result[] = $schemeRoutesDefinitionsBlock;
            }
        }

        return $result;
    }

    /**
     * Returns template's placeholders values.
     *
     * @return array
     * @throws Exception
     */
    protected function getPlaceHoldersValues(): array
    {
        $routesDefinitions = $this->codeFormatter->linesToBlock($this->getRoutesDefinition());

        return [
            static::PLACEHOLDER_CONTROLLERS_NAMESPACE => $this->config->controllersNamespace,
            static::PLACEHOLDER_API_ROUTES_DEFINITIONS => $this->codeFormatter->indentBlock($routesDefinitions),
        ];
    }
}
