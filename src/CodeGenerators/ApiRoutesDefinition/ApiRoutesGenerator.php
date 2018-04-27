<?php

namespace Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition;

use Saritasa\LaravelTools\DTO\Routes\ApiRouteObject;

/**
 * Api routes generation methods facade. Allows to render route, routes block and routes group definition.
 */
class ApiRoutesGenerator
{
    /**
     * Api route generator. Allows to build route declaration with description according to route details.
     *
     * @var IApiRouteGenerator
     */
    private $routeGenerator;

    /**
     * Api routes block generator. Allows to build routes block (not routes group) with description.
     *
     * @var IApiRoutesBlockGenerator
     */
    private $blockGenerator;

    /**
     * Api routes group generator. Allows to generate api routes group declaration.
     *
     * @var IApiRoutesGroupGenerator
     */
    private $groupGenerator;

    /**
     * Api routes generation methods facade. Allows to render route, routes block and routes group definition.
     *
     * @param IApiRouteGenerator $routeGenerator Api route generator. Allows to build route declaration with
     *     description according to route details
     * @param IApiRoutesBlockGenerator $blockGenerator Api routes block generator. Allows to build routes block (not
     *     routes group) with description
     * @param IApiRoutesGroupGenerator $groupGenerator Api routes group generator. Allows to generate api routes group
     *     declaration
     */
    public function __construct(
        IApiRouteGenerator $routeGenerator,
        IApiRoutesBlockGenerator $blockGenerator,
        IApiRoutesGroupGenerator $groupGenerator
    ) {
        $this->routeGenerator = $routeGenerator;
        $this->blockGenerator = $blockGenerator;
        $this->groupGenerator = $groupGenerator;
    }

    /**
     * Allows to generate api routes group declaration.
     *
     * @param string $groupContent Routes content that should be wrapped into group
     * @param array|null $middleware Middlewares collection that should be applied to routes group
     * @param null|string $description Routes group description
     *
     * @return string
     * @see IApiRoutesGroupGenerator::render()
     */
    public function renderGroup(string $groupContent, ?array $middleware = null, ?string $description = null): string
    {
        return $this->groupGenerator->render($groupContent, $middleware, $description);
    }

    /**
     * Renders list of api routers objects as block of routes definitions.
     *
     * @param ApiRouteObject[] $apiEndpoints Endpoints to render
     * @param null|string $blockDescription Block of routes description if any
     *
     * @return string
     * @see IApiRoutesBlockGenerator::render()
     */
    public function renderBlock(array $apiEndpoints, ?string $blockDescription = null): string
    {
        return $this->blockGenerator->render($apiEndpoints, $blockDescription);
    }

    /**
     * Renders api route definition.
     *
     * @param ApiRouteObject $routeData Route details to build route definition
     *
     * @return string
     * @see IApiRouteGenerator::render()
     */
    public function renderRoute(ApiRouteObject $routeData): string
    {
        return $this->routeGenerator->render($routeData);
    }
}
