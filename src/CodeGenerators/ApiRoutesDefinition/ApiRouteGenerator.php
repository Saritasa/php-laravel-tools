<?php

namespace Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition;

use Saritasa\LaravelTools\DTO\ApiRouteObject;

/**
 * Api route generator. Allows to build route declaration with description according to route details.
 */
class ApiRouteGenerator
{
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
        $method = strtolower($routeData->method);

        return "\$api->{$method}('{$routeData->url}', '')->name('');";
    }
}
