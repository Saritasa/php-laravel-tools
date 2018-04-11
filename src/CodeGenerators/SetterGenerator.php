<?php

namespace Saritasa\LaravelTools\CodeGenerators;

use Saritasa\LaravelTools\Mappings\PhpToPhpDocTypeMapper;

/**
 * Setter Generator class. Allows to generate setter function declaration for given attribute and type.
 */
class SetterGenerator
{
    /**
     * Php scalar type to PhpDoc scalar type mapper.
     *
     * @var PhpToPhpDocTypeMapper
     */
    private $phpToPhpDocTypeMapper;

    /**
     * Setter Generator class. Allows to generate setter function declaration for given attribute and type.
     *
     * @param PhpToPhpDocTypeMapper $phpToPhpDocTypeMapper Php scalar type to PhpDoc scalar type mapper
     */
    public function __construct(PhpToPhpDocTypeMapper $phpToPhpDocTypeMapper)
    {
        $this->phpToPhpDocTypeMapper = $phpToPhpDocTypeMapper;
    }

    /**
     * Allows to generate setter function declaration for given attribute and type.
     *
     * @param string $attributeName Attribute name for which need to generate setter
     * @param string $attributeType Attribute type to typehint value
     * @param string $visibilityType Function visibility type. Public or protected, for example
     * @param bool $nullable Determines is new value can be NULL
     *
     * @return string
     */
    public function render(
        string $attributeName,
        string $attributeType,
        string $visibilityType = 'public',
        bool $nullable = false
    ): string {
        $phpDocType = $this->phpToPhpDocTypeMapper->getPhpDocType($attributeType);

        return implode("\n", [
            $this->getDescription($attributeName, $phpDocType, $nullable),
            $this->getDeclaration($attributeName, $attributeType, $visibilityType, $nullable),
        ]);
    }

    /**
     * Returns setter function declaration.
     *
     * @param string $attributeName Attribute name for which need to generate setter
     * @param string $attributeType Attribute type to typehint value
     * @param string $visibilityType Function visibility type. Public or protected, for example
     * @param bool $nullable Determines is new value can be NULL
     *
     * @return string
     */
    protected function getDeclaration(
        string $attributeName,
        string $attributeType,
        string $visibilityType,
        bool $nullable = false
    ): string {
        $setterFunctionName = 'set' . studly_case($attributeName);

        $attributeType = ($nullable ? '?' : '') . $attributeType;

        return <<<template
{$visibilityType} function {$setterFunctionName}({$attributeType} \${$attributeName}): void
{
    \$this->{$attributeName} = \${$attributeName};
}
template;
    }

    /**
     * Returns setter function description.
     *
     * @param string $attributeName Attribute name for which need to generate setter
     * @param string $attributeType Attribute type to typehint value
     * @param bool $nullable Determines is new value can be NULL
     *
     * @return string
     */
    protected function getDescription(string $attributeName, string $attributeType, bool $nullable = false): string
    {
        $attributeType .= $nullable ? '|null' : '';

        return <<<template
/**
 * Set {$attributeName} attribute value.
 *
 * @param {$attributeType} \${$attributeName} New attribute value
 *
 * @return void
 */
template;
    }
}
