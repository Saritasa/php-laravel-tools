<?php

namespace Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition;

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
     * Api route generator. Allows to build route declaration with description according to route details.
     *
     * @param ApiRoutesImplementationGuesser $apiRoutesImplementationGuesser Api route implementation guesser that can
     *     guess which controller, method and name should be used for api route specification.
     */
    public function __construct(ApiRoutesImplementationGuesser $apiRoutesImplementationGuesser)
    {
        $this->apiRoutesImplementationGuesser = $apiRoutesImplementationGuesser;
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
        $description = ucfirst(trim($routeData->description));
        if (!$description) {
            return null;
        }

        return "// {$description}";
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
