<?php

namespace Saritasa\LaravelTools\CodeGenerators\PhpDoc;

use Saritasa\LaravelTools\DTO\ClassPropertyObject;
use Saritasa\LaravelTools\Mappings\PhpToPhpDocTypeMapper;

/**
 * PhpDoc for variable builder. Allows to generate PhpDoc variable description block.
 */
class PhpDocVariableDescriptionBuilder
{
    /**
     * Php scalar type to PhpDoc scalar type mapper.
     *
     * @var PhpToPhpDocTypeMapper
     */
    private $phpToPhpDocTypeMapper;

    /**
     * PhpDoc for variable builder. Allows to generate PhpDoc variable description block.
     *
     * @param PhpToPhpDocTypeMapper $phpToPhpDocTypeMapper Php scalar type to PhpDoc scalar type mapper
     */
    public function __construct(PhpToPhpDocTypeMapper $phpToPhpDocTypeMapper)
    {
        $this->phpToPhpDocTypeMapper = $phpToPhpDocTypeMapper;
    }

    /**
     * Return PhpDoc variable description block.
     *
     * @param ClassPropertyObject $classProperty Class property details
     *
     * @return string
     */
    public function render(ClassPropertyObject $classProperty): string
    {
        $nullableType = $classProperty->nullable
            ? '|null'
            : '';

        $phpDocType = $this->phpToPhpDocTypeMapper->getPhpDocType($classProperty->type);

        return <<<DESCRIPTION
/**
 * {$classProperty->description}.
 *
 * @var {$phpDocType}{$nullableType}
 */
DESCRIPTION;
    }
}
