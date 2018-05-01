<?php

namespace Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition;

use Saritasa\LaravelTools\DTO\Routes\ApiRouteImplementationObject;
use Saritasa\LaravelTools\DTO\Routes\ApiRouteObject;

/**
 * Api route via api resource registrar generator. Allows to build route declaration with description according to
 * route details. Extends route declaration with custom model class binding.
 */
class ApiRouteModelBindingResourceRegistrarGenerator extends ApiRouteResourceRegistrarGenerator
{
    /**
     * Detects binding in route by resource model class.
     *
     * @param ApiRouteImplementationObject $routeImplementation Route implementation to detect bindings
     *
     * @return boolean
     */
    private function modelBindingRequired(ApiRouteImplementationObject $routeImplementation): bool
    {
        foreach ($routeImplementation->function->parameters as $parameter) {
            return $parameter->name === 'model';
        }

        return false;
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

        $resourceBindingSubstituted = $this->modelBindingRequired($routeImplementation);

        $url = $routeData->url;
        $routeBindings = '';

        if ($resourceBindingSubstituted) {
            $url = str_replace_first('{id}', '{model}', $routeData->url);
            $routeBindings = ", ['model' => {$routeImplementation->resourceClass}::class]";
        }

        $method = strtolower($routeData->method);

        return "\$registrar->{$method}(" .
            "'{$url}', " .
            "{$routeImplementation->controller}::class, " .
            "'{$routeImplementation->action}', " .
            "'{$routeImplementation->name}'" .
            "{$routeBindings});";
    }
}
