<?php

namespace Saritasa\LaravelTools\Services;

use Illuminate\Config\Repository;
use Illuminate\Support\Str;
use Saritasa\LaravelTools\DTO\PhpClasses\FunctionObject;
use Saritasa\LaravelTools\DTO\PhpClasses\FunctionParameterObject;
use Saritasa\LaravelTools\DTO\Routes\ApiRouteImplementationObject;
use Saritasa\LaravelTools\DTO\Routes\ApiRouteObject;
use Saritasa\LaravelTools\DTO\Routes\KnownApiRouteObject;
use Saritasa\LaravelTools\Enums\ClassMemberVisibilityTypes;

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
    private $controllerNameSuffix;

    /**
     * List of known routes parameters details such as controller, controller's method and route name
     *
     * @var KnownApiRouteObject[]
     */
    private $knownRoutes = [];

    /**
     * Namespace of models that are guessed as handled by route resources.
     *
     * @var string
     */
    private $modelsNamespace;

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
        $this->controllerNameSuffix = $configRepository
            ->get('laravel_tools.api_controllers.name_suffix');
        $this->knownRoutes = $configRepository
            ->get('laravel_tools.api_routes.known_routes', []);
        $this->modelsNamespace = $configRepository
            ->get('laravel_tools.models.namespace');
    }

    /**
     * Guess handled by route resource name.
     *
     * @param ApiRouteObject $route Route to retrieve resource name
     *
     * @return string
     */
    private function guessRouteResourceName(ApiRouteObject $route): ?string
    {
        $resource = $route->group;

        if (!$resource) {
            $resource = explode('/', ltrim($route->url, '/'))[0] ?? null;
        }

        if (!$resource) {
            return null;
        }

        return Str::camel(Str::plural(trim($resource)));
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

        return Str::studly($this->guessRouteResourceName($route)) . $this->controllerNameSuffix;
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

        if ($knownRoute->action) {
            return $knownRoute->action;
        }

        if ($route->operationId) {
            $methodName = $route->operationId;
        } else {
            $methodName = strtolower($route->method);
            $resourceRegexp = '/([a-zA-Z]*)\/\{id\}/';
            $matches = [];
            $url = $route->url;
            if (preg_match($resourceRegexp, $route->url, $matches)) {
                $resource = $matches[1];
                $methodName .= '_' . Str::singular($resource);
                $url = str_replace($matches[0], '', $url);
            }
            $parametersRegexp = '/(\{[^\}.]*\})/';
            $url = preg_replace($parametersRegexp, '', $url);
            $url = str_replace('/', '_', $url);
            $methodName .= Str::studly($url);
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
     * Guess handled by route resource name.
     *
     * @param ApiRouteObject $route Route to retrieve resource information
     *
     * @return null|string
     */
    private function guessResourceClass(ApiRouteObject $route): ?string
    {
        $resourceName = $this->guessRouteResourceName($route);

        if (!$resourceName) {
            return null;
        }

        $resourceClassName = $this->modelsNamespace . '\\' . Str::studly(Str::singular($resourceName));

        if (class_exists($resourceClassName)) {
            return $resourceClassName;
        }

        return null;
    }

    /**
     * Guess function details that probably can handle this route.
     *
     * @param ApiRouteObject $route Route to retrieve function details
     *
     * @return FunctionObject
     */
    private function guessFunctionDetails(ApiRouteObject $route): FunctionObject
    {
        $parameters = [];
        foreach ($route->parameters as $parameter) {
            if ($parameter->in !== 'path') {
                continue;
            }

            $parameters[] = new FunctionParameterObject([
                FunctionParameterObject::DESCRIPTION => $parameter->description,
                FunctionParameterObject::NAME => $parameter->name,
                FunctionParameterObject::TYPE => $parameter->type,
                FunctionParameterObject::NULLABLE => !$parameter->required,
            ]);
        }

        return new FunctionObject([
            FunctionObject::NAME => $this->guessMethodName($route),
            FunctionObject::DESCRIPTION => $route->description,
            FunctionObject::VISIBILITY_TYPE => ClassMemberVisibilityTypes::PUBLIC,
            FunctionObject::PARAMETERS => $parameters,
        ]);
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
            ApiRouteImplementationObject::ACTION => $this->guessMethodName($route),
            ApiRouteImplementationObject::NAME => $this->guessRouteName($route),
            ApiRouteImplementationObject::FUNCTION => $this->guessFunctionDetails($route),
            ApiRouteImplementationObject::RESOURCE_CLASS => $this->guessResourceClass($route),
        ]);
    }
}
