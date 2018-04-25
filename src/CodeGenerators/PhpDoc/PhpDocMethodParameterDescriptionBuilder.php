<?php

namespace Saritasa\LaravelTools\CodeGenerators\PhpDoc;

use Saritasa\LaravelTools\DTO\PhpClasses\FunctionParameterObject;
use Saritasa\LaravelTools\Enums\PhpDocPropertyAccessTypes;
use Saritasa\LaravelTools\Mappings\PhpToPhpDocTypeMapper;

/**
 * PhpDoc method parameter builder. Allows to generate PhpDoc for method parameter with type, name and description.
 */
class PhpDocMethodParameterDescriptionBuilder
{
    /**
     * Php scalar type to PhpDoc scalar type mapper.
     *
     * @var PhpToPhpDocTypeMapper
     */
    private $phpToPhpDocTypeMapper;

    /**
     * PhpDoc property line builder. Allows to generate PhpDoc property line with variable type and description.
     *
     * @param PhpToPhpDocTypeMapper $phpToPhpDocTypeMapper Php scalar type to PhpDoc scalar type mapper
     */
    public function __construct(PhpToPhpDocTypeMapper $phpToPhpDocTypeMapper)
    {
        $this->phpToPhpDocTypeMapper = $phpToPhpDocTypeMapper;
    }

    /**
     * Return PhpDoc property line description.
     *
     * @param FunctionParameterObject $methodParameter Class property details
     *
     * @return string
     * @see PhpDocPropertyAccessTypes for available propety access types details
     */
    public function render(FunctionParameterObject $methodParameter): string
    {
        $nullableType = $methodParameter->nullable
            ? '|null'
            : '';

        $phpDocType = $this->phpToPhpDocTypeMapper->getPhpDocType($methodParameter->type);

        return "@param " . trim(
                "{$phpDocType}{$nullableType} \${$methodParameter->name} " .
                "{$methodParameter->description}"
            );
    }
}
