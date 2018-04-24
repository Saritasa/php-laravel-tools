<?php

namespace Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition;

use Illuminate\Config\Repository;
use Illuminate\Support\Str;
use Saritasa\LaravelTools\DTO\Routes\ApiRouteImplementationObject;
use Saritasa\LaravelTools\DTO\Routes\ApiRouteObject;
use Saritasa\LaravelTools\DTO\Routes\KnownApiRouteObject;

/**
 * Api route implementation guesser that can guess which controller, method and name should be used for api route
 * specification.
 */
class ApiRoutesImplementationGuesser
{
    private const KNOWN_ROUTE_RESOURCE_NAME_PLACEHOLDER = '{{resourceName}}';

    /**
     * Suffix that should be used for generated controller names.
     *
     * @var string
     */
    private $generatedControllerSuffix;

    /**
     * List of known routes parameters details such as controller, controller's method and route name
     *
     * @var KnownApiRouteObject[]
     */
    private $knownRoutes = [];

    /**
     * Api route implementation guesser that can guess which controller, method and name should be used for api route
     * specification.
     *
     * @param Repository $configRepository Configuration storage
     *
     * @return void
     */
    public function __construct(Repository $configRepository)
    {
        $this->generatedControllerSuffix = $configRepository
            ->get('laravel_tools.api_controllers.generated_controller_suffix');
        $this->knownRoutes = $configRepository
            ->get('laravel_tools.api_routes.known_routes', []);
    }

    /**
     * Guess handled by route resource name.
     *
     * @param ApiRouteObject $route Route to retrieve resource name
     *
     * @return string
     */
    private function guessRouteResourceName(ApiRouteObject $route): string
    {
        return Str::camel(Str::plural(trim($route->group)));
    }

    /**
     * Returns well-known route details.
     *
     * @param ApiRouteObject $route Route to retrieve well-known route details
     *
     * @return KnownApiRouteObject
     */
    private function getKnownRoute(ApiRouteObject $route): KnownApiRouteObject
    {
        $resourceName = $this->guessRouteResourceName($route);
        $methodRoutes = $this->knownRoutes[$route->method] ?? [];
        foreach ($methodRoutes as $url => $routeImplementationDetails) {
            if (Str::contains($url, static::KNOWN_ROUTE_RESOURCE_NAME_PLACEHOLDER)) {
                $url = str_replace(static::KNOWN_ROUTE_RESOURCE_NAME_PLACEHOLDER, $resourceName, $url);
            }
            if ($route->url === $url) {
                return new KnownApiRouteObject($routeImplementationDetails);
            }
        }

        return new KnownApiRouteObject([]);
    }

    /**
     * Returns appropriate controller name for api route implementation.
     *
     * @param ApiRouteObject $route Route to guess controller name
     *
     * @return string
     */
    private function guessControllerName(ApiRouteObject $route): string
    {
        $knownRoute = $this->getKnownRoute($route);

        if ($knownRoute->controller) {
            return $knownRoute->controller;
        }

        return Str::studly($this->guessRouteResourceName($route)) . $this->generatedControllerSuffix;
    }

    /**
     * Returns appropriate method name for api route implementation.
     *
     * @param ApiRouteObject $route Route to guess method name
     *
     * @return string
     */
    private function guessMethodName(ApiRouteObject $route): string
    {
        $knownRoute = $this->getKnownRoute($route);

        if ($knownRoute->method) {
            return $knownRoute->method;
        }

        if ($route->operationId) {
            $methodName = $route->operationId;
        } else {
            $parametersRegexp = '/(\{[^\}.]*\})/';
            $url = preg_replace($parametersRegexp, '', $route->url);
            $url = str_replace('/', '_', $url);
            $methodName = strtolower($route->method) . Str::studly($url);
        }

        return Str::camel($methodName);
    }

    /**
     * Returns appropriate route name for api route implementation.
     *
     * @param ApiRouteObject $route Route to retrieve route name
     *
     * @return string
     */
    private function guessRouteName(ApiRouteObject $route): string
    {
        $knownRoute = $this->getKnownRoute($route);
        $resourceName = $this->guessRouteResourceName($route);

        if ($knownRoute->name) {
            return str_replace(static::KNOWN_ROUTE_RESOURCE_NAME_PLACEHOLDER, $resourceName, $knownRoute->name);
        }

        $methodName = $this->guessMethodName($route);

        return "{$resourceName}.{$methodName}";
    }

    /**
     * Returns guessed route implementation details.
     *
     * @param ApiRouteObject $route Route to retrieve implementation details
     *
     * @return ApiRouteImplementationObject
     */
    public function getRouteImplementationDetails(ApiRouteObject $route): ApiRouteImplementationObject
    {
        return new ApiRouteImplementationObject([
            ApiRouteImplementationObject::CONTROLLER => $this->guessControllerName($route),
            ApiRouteImplementationObject::METHOD => $this->guessMethodName($route),
            ApiRouteImplementationObject::NAME => $this->guessRouteName($route),
        ]);
    }
}