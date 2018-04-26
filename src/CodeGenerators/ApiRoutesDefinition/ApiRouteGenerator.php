<?php

namespace Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition;

use Saritasa\LaravelTools\CodeGenerators\CodeFormatter;
use Saritasa\LaravelTools\CodeGenerators\CommentsGenerator;
use Saritasa\LaravelTools\DTO\Routes\ApiRouteObject;

/**
 * Api route generator. Allows to build route declaration with description according to route details.
 */
class ApiRouteGenerator
{
    /**
     * Api route implementation guesser that can guess which controller, method and name should be used for api route
     * specification.
     *
     * @var ApiRoutesImplementationGuesser
     */
    private $apiRoutesImplementationGuesser;

    /**
     * Php comments generator. Allows to comment lines and blocks of text.
     *
     * @var CommentsGenerator
     */
    private $commentsGenerator;

    /**
     * Code style utility. Allows to format code according to settings. Can apply valid indent to code line or code
     * block.
     *
     * @var CodeFormatter
     */
    private $codeFormatter;

    /**
     * Api route generator. Allows to build route declaration with description according to route details.
     *
     * @param ApiRoutesImplementationGuesser $apiRoutesImplementationGuesser Api route implementation guesser that can
     *     guess which controller, method and name should be used for api route specification.
     * @param CommentsGenerator $commentsGenerator Php comments generator. Allows to comment lines and blocks of text
     * @param CodeFormatter $codeFormatter Code style utility. Allows to format code according to settings. Can apply
     *     valid indent to code line or code block
     */
    public function __construct(
        ApiRoutesImplementationGuesser $apiRoutesImplementationGuesser,
        CommentsGenerator $commentsGenerator,
        CodeFormatter $codeFormatter
    ) {
        $this->apiRoutesImplementationGuesser = $apiRoutesImplementationGuesser;
        $this->commentsGenerator = $commentsGenerator;
        $this->codeFormatter = $codeFormatter;
    }

    /**
     * Renders api route definition.
     *
     * @param ApiRouteObject $routeData Route details to build route definition
     *
     * @return string
     */
    public function render(ApiRouteObject $routeData): string
    {
        $description = $this->getDescription($routeData);
        $declaration = $this->getDeclaration($routeData);

        return trim("{$description}\n{$declaration}");
    }

    /**
     * Returns description for route.
     *
     * @param ApiRouteObject $routeData Route data to retrieve description
     *
     * @return null|string
     */
    protected function getDescription(ApiRouteObject $routeData): ?string
    {
        if (!$routeData->description) {
            return null;
        }

        return $this->commentsGenerator->line($this->codeFormatter->toSentence($routeData->description, false));
    }

    /**
     * Returns API route declaration.
     *
     * @param ApiRouteObject $routeData Route data to build route declarations
     *
     * @return string
     */
    protected function getDeclaration(ApiRouteObject $routeData): string
    {
        $routeImplementation = $this->apiRoutesImplementationGuesser->getRouteImplementationDetails($routeData);
        $method = strtolower($routeData->method);

        $routeAction = "{$routeImplementation->controller}@{$routeImplementation->action}";

        return "\$api->{$method}('{$routeData->url}', '{$routeAction}')->name('{$routeImplementation->name}');";
    }
}
