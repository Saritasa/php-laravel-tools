<?php

namespace Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition;

use Saritasa\LaravelTools\CodeGenerators\CodeStyler;

/**
 * Api routes group generator. Allows to generate api routes group declaration.
 */
class ApiRoutesGroupGenerator
{
    /**
     * Generated code styler helper. Allows to apply indent to code.
     *
     * @var CodeStyler
     */
    private $codeStyler;

    /**
     * Api routes group generator. Allows to generate api routes group declaration.
     *
     * @param CodeStyler $codeStyler Generated code styler helper. Allows to apply indent to code
     */
    public function __construct(CodeStyler $codeStyler)
    {
        $this->codeStyler = $codeStyler;
    }

    /**
     * Allows to generate api routes group declaration.
     *
     * @param string $groupContent Routes content that should be wrapped into group
     * @param array|null $middleware Middlewares collection that should be applied to routes group
     * @param null|string $description Routes group description
     *
     * @return string
     */
    public function render(string $groupContent, ?array $middleware = null, ?string $description = null): string
    {
        $description = $this->getGroupDescription($description);
        $declaration = $this->getGroupDeclaration($groupContent, $middleware);

        return trim("{$description}\n${declaration}");
    }

    /**
     * Returns routes group definition
     *
     * @param string $groupContent Routes content that should be wrapped into group
     * @param array|null $middleware Array of route group middlewares
     *
     * @return string
     */
    protected function getGroupDeclaration(string $groupContent, ?array $middleware = null): string
    {
        $groupOptions = '[]';
        if ($middleware) {
            $groupOptions = '[\'middleware\' => [\'' . implode('\', \'', $middleware) . '\']]';
        }

        $indentedGroupContent = $this->codeStyler->indentBlock($groupContent);

        return <<<GROUP
\$api->group({$groupOptions}, function (Router \$api) {
{$indentedGroupContent}
});
GROUP;
    }

    /**
     * Returns formatted description block for routes group.
     *
     * @param null|string $description Description to format
     *
     * @return null|string
     */
    protected function getGroupDescription(?string $description = null): ?string
    {
        $formattedDescription = ucfirst(trim($description));

        if (!$formattedDescription) {
            return null;
        }

        return "// {$formattedDescription}";
    }
}
