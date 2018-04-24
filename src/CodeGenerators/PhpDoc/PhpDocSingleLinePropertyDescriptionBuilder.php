<?php

namespace Saritasa\LaravelTools\CodeGenerators\PhpDoc;

use Saritasa\LaravelTools\DTO\PhpClasses\ClassPropertyObject;
use Saritasa\LaravelTools\Enums\PhpDocPropertyAccessTypes;
use Saritasa\LaravelTools\Mappings\PhpToPhpDocTypeMapper;

/**
 * PhpDoc property line builder. Allows to generate PhpDoc property line with variable type and description.
 */
class PhpDocSingleLinePropertyDescriptionBuilder
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
     * @param \Saritasa\LaravelTools\DTO\PhpClasses\ClassPropertyObject $classProperty Class property details
     *
     * @return string
     * @see PhpDocPropertyAccessTypes for available propety access types details
     */
    public function render(ClassPropertyObject $classProperty): string
    {
        $nullableType = $classProperty->nullable
            ? '|null'
            : '';
        switch ($classProperty->accessType) {
            case PhpDocPropertyAccessTypes::READ:
                $accessModifier = '-read';
                break;
            case PhpDocPropertyAccessTypes::WRITE:
                $accessModifier = '-write';
                break;
            default:
                $accessModifier = '';
                break;
        }

        $phpDocType = $this->phpToPhpDocTypeMapper->getPhpDocType($classProperty->type);

        return rtrim(
            " * @property{$accessModifier} " .
            "{$phpDocType}{$nullableType} \${$classProperty->name} " .
            "{$classProperty->description}"
        );
    }
}
