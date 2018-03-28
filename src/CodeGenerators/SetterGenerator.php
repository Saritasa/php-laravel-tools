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
     *
     * @return string
     */
    public function render(string $attributeName, string $attributeType, string $visibilityType = 'public'): string
    {
        $phpDocType = $this->phpToPhpDocTypeMapper->getPhpDocType($attributeType);

        return implode("\n", [
            $this->getDescription($attributeName, $phpDocType),
            $this->getDeclaration($attributeName, $attributeType, $visibilityType),
        ]);
    }

    /**
     * Returns setter function declaration.
     *
     * @param string $attributeName Attribute name for which need to generate setter
     * @param string $attributeType Attribute type to typehint value
     * @param string $visibilityType Function visibility type. Public or protected, for example
     *
     * @return string
     */
    protected function getDeclaration(string $attributeName, string $attributeType, string $visibilityType): string
    {
        $setterFunctionName = 'set' . ucfirst($attributeName);

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
     *
     * @return string
     */
    protected function getDescription(string $attributeName, string $attributeType): string
    {
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
