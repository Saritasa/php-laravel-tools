<?php

namespace Saritasa\LaravelTools\CodeGenerators;

use Saritasa\LaravelTools\Mappings\PhpToPhpDocTypeMapper;

/**
 * Getter Generator class. Allows to generate getter function declaration for given attribute and type.
 */
class GetterGenerator
{
    /**
     * Php scalar type to PhpDoc scalar type mapper.
     *
     * @var PhpToPhpDocTypeMapper
     */
    private $phpToPhpDocTypeMapper;

    /**
     * Getter Generator class. Allows to generate getter function declaration for given attribute and type.
     *
     * @param PhpToPhpDocTypeMapper $phpToPhpDocTypeMapper Php scalar type to PhpDoc scalar type mapper
     */
    public function __construct(PhpToPhpDocTypeMapper $phpToPhpDocTypeMapper)
    {
        $this->phpToPhpDocTypeMapper = $phpToPhpDocTypeMapper;
    }

    /**
     * Allows to generate getter function declaration for given attribute and type.
     *
     * @param string $attributeName Attribute name for which need to generate getter
     * @param string $attributeType Attribute type to typehint getter
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
     * Returns getter function declaration.
     *
     * @param string $attributeName Attribute name for which need to generate getter
     * @param string $attributeType Attribute type to typehint getter
     * @param string $visibilityType Function visibility type. Public or protected, for example
     *
     * @return string
     */
    protected function getDeclaration(string $attributeName, string $attributeType, string $visibilityType): string
    {
        $getterFunctionName = 'get' . ucfirst($attributeName);

        return <<<template
{$visibilityType} function {$getterFunctionName}(): {$attributeType}
{
    return \$this->{$attributeName};
}
template;
    }

    /**
     * Returns getter function description.
     *
     * @param string $attributeName Attribute name for which need to generate getter
     * @param string $attributeType Attribute type to typehint getter
     *
     * @return string
     */
    protected function getDescription(string $attributeName, string $attributeType): string
    {
        return <<<template
/**
 * Get {$attributeName} attribute value.
 *
 * @return {$attributeType}
 */
template;
    }
}
