<?php

namespace Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition;

use Saritasa\LaravelTools\DTO\Routes\ApiRouteObject;

/**
 * Api routes block generator. Allows to build routes block (not routes group) with description.
 */
interface IApiRoutesBlockGenerator
{
    /**
     * Renders list of api routers objects as block of routes definitions.
     *
     * @param ApiRouteObject[] $apiEndpoints Endpoints to render
     * @param null|string $blockDescription Block of routes description if any
     *
     * @return string
     */
    public function render(array $apiEndpoints, ?string $blockDescription = null): string;
}
