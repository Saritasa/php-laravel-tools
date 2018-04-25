<?php

namespace Saritasa\LaravelTools\CodeGenerators;

use Saritasa\LaravelTools\CodeGenerators\PhpDoc\PhpDocMethodParameterDescriptionBuilder;
use Saritasa\LaravelTools\DTO\PhpClasses\FunctionObject;
use Saritasa\LaravelTools\Mappings\PhpToPhpDocTypeMapper;

/**
 * Php function code generator. Allows to generate function declaration by parameters.
 */
class FunctionGenerator
{
    /**
     * Code style utility. Allows to format code according to settings. Can apply valid indent to code line or code
     * block.
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
     * PhpDoc method parameter builder. Allows to generate PhpDoc for method parameter with type, name and description.
     *
     * @var PhpDocMethodParameterDescriptionBuilder
     */
    private $methodParameterDescriptionBuilder;

    /**
     * Php scalar type to PhpDoc scalar type mapper.
     *
     * @var PhpToPhpDocTypeMapper
     */
    private $phpToPhpDocTypeMapper;

    /**
     * Php function code generator. Allows to generate function declaration by parameters.
     *
     * @param CodeFormatter $codeFormatter Code style utility. Allows to format code according to settings. Can apply
     *     valid indent to code line or code block
     * @param CommentsGenerator $commentsGenerator Php comments generator. Allows to comment lines and blocks of text
     * @param PhpToPhpDocTypeMapper $phpToPhpDocTypeMapper Php scalar type to PhpDoc scalar type mapper
     * @param PhpDocMethodParameterDescriptionBuilder $methodParameterDescriptionBuilder PhpDoc method parameter
     *     builder. Allows to generate PhpDoc for method parameter with type, name and description
     */
    public function __construct(
        CodeFormatter $codeFormatter,
        CommentsGenerator $commentsGenerator,
        PhpToPhpDocTypeMapper $phpToPhpDocTypeMapper,
        PhpDocMethodParameterDescriptionBuilder $methodParameterDescriptionBuilder
    ) {
        $this->codeFormatter = $codeFormatter;
        $this->commentsGenerator = $commentsGenerator;
        $this->methodParameterDescriptionBuilder = $methodParameterDescriptionBuilder;
        $this->phpToPhpDocTypeMapper = $phpToPhpDocTypeMapper;
    }

    /**
     * Render function code by function parameters.
     *
     * @param FunctionObject $functionObject Function details to render code.
     *
     * @return string
     */
    public function render(FunctionObject $functionObject): string
    {
        $result = [
            $this->getDescription($functionObject),
            $this->getDeclaration($functionObject),
        ];

        return $this->codeFormatter->linesToBlock($result);
    }

    /**
     * Returns function description doc block.
     *
     * @param FunctionObject $functionObject Function details to retrieve description
     *
     * @return string
     */
    private function getDescription(FunctionObject $functionObject): string
    {
        $descriptionLines = [];
        $descriptionLines[] = $this->codeFormatter->toSentence($functionObject->description);
        if ($functionObject->parameters) {
            $descriptionLines[] = '';
            foreach ($functionObject->parameters as $parameter) {
                $descriptionLines[] = $this->methodParameterDescriptionBuilder->render($parameter);
            }
        }

        if ($functionObject->returnType) {
            $descriptionLines[] = '';
            $returnType = $this->phpToPhpDocTypeMapper->getPhpDocType($functionObject->returnType);
            if ($functionObject->nullableResult) {
                $returnType .= '|null';
            }
            $descriptionLines[] = "@return {$returnType}";
        }

        return $this->commentsGenerator->block($this->codeFormatter->linesToBlock($descriptionLines));
    }

    /**
     * Returns function declaration.
     *
     * @param FunctionObject $functionObject Function details to build body
     *
     * @return string
     */
    private function getDeclaration(FunctionObject $functionObject): string
    {
        $parameters = [];
        foreach ($functionObject->parameters as $parameter) {
            $parameterString = '';
            if ($parameter->type) {
                if ($parameter->nullable) {
                    $parameterString .= '?';
                }
                $parameterString .= $parameter->type;
                $parameterString .= ' ';
            }
            $parameterString .= "\${$parameter->name}";
            if ($parameter->default) {
                $parameterString .= " = {$parameter->default}";
            }
            $parameters[] = $parameterString;
        }

        $parametersList = implode(', ', $parameters);
        $nullableResultPrefix = $functionObject->nullableResult ? '?' : '';
        $returnType = $functionObject->returnType
            ? ": {$nullableResultPrefix}{$functionObject->returnType}"
            : '';
        $functionBody = $this->codeFormatter->indentBlock($functionObject->content
            ? $functionObject->content
            : '// Do not forget to fill function body');

        return <<<DECLARATION
{$functionObject->visibilityType} function {$functionObject->name}({$parametersList}){$returnType}
{
{$functionBody}
}
DECLARATION;
    }
}
