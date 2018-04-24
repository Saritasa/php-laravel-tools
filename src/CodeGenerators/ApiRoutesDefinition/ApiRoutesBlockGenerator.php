<?php

namespace Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition;

use Saritasa\LaravelTools\CodeGenerators\CodeFormatter;
use Saritasa\LaravelTools\CodeGenerators\CommentsGenerator;
use Saritasa\LaravelTools\DTO\Routes\ApiRouteObject;

/**
 * Api routes block generator. Allows to build routes block (not routes group) with description.
 */
class ApiRoutesBlockGenerator
{
    /**
     * Generated code formatter helper. Allows to apply indent to code.
     *
     * @var CodeFormatter
     */
    private $codeFormatter;

    /**
     * Php comments generator. Allows to comment lines and blocks of text.
     *
     * @var CommentsGenerator
     */
    private $commentsGenerator;

    /**
     * Api route generator. Allows to build route declaration with description according to route details.
     *
     * @var ApiRouteGenerator
     */
    private $apiRouteGenerator;

    /**
     * Api routes block generator. Allows to build routes block (not routes group) with description.
     *
     * @param CodeFormatter $codeFormatter Generated code formatter helper. Allows to apply indent to code
     * @param CommentsGenerator $commentsGenerator Php comments generator. Allows to comment lines and blocks of text
     * @param ApiRouteGenerator $apiRouteGenerator Api route generator. Allows to build route declaration with
     *     description according to route details
     */
    public function __construct(
        CodeFormatter $codeFormatter,
        CommentsGenerator $commentsGenerator,
        ApiRouteGenerator $apiRouteGenerator
    ) {
        $this->codeFormatter = $codeFormatter;
        $this->commentsGenerator = $commentsGenerator;
        $this->apiRouteGenerator = $apiRouteGenerator;
    }

    /**
     * Renders list of api routers objects as block of routes definitions.
     *
     * @param ApiRouteObject[] $apiEndpoints Endpoints to render
     * @param null|string $blockDescription Block of routes description if any
     *
     * @return string
     */
    public function render(array $apiEndpoints, ?string $blockDescription = null): string
    {
        $blockDefinition = [];
        if ($blockDescription) {
            $blockDefinition[] = $this->commentsGenerator->alternativeBlock($blockDescription);
            $blockDefinition[] = '';
        }
        foreach ($apiEndpoints as $route) {
            $blockDefinition[] = $this->apiRouteGenerator->render($route);
        }

        return $this->codeFormatter->linesToBlock($blockDefinition);
    }
}
