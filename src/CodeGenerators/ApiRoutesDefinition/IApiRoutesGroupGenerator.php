<?php

namespace Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition;

/**
 * Api routes group generator. Allows to generate api routes group declaration.
 */
interface IApiRoutesGroupGenerator
{
    /**
     * Allows to generate api routes group declaration.
     *
     * @param string $groupContent Routes content that should be wrapped into group
     * @param array|null $middleware Middlewares collection that should be applied to routes group
     * @param null|string $description Routes group description
     *
     * @return string
     */
    public function render(string $groupContent, ?array $middleware = null, ?string $description = null): string;
}
