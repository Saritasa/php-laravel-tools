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
     * @param bool $nullable Determines is returned value can be NULL
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
     * Returns getter function declaration.
     *
     * @param string $attributeName Attribute name for which need to generate getter
     * @param string $attributeType Attribute type to typehint getter
     * @param string $visibilityType Function visibility type. Public or protected, for example
     * @param bool $nullable Determines is returned value can be NULL
     *
     * @return string
     */
    protected function getDeclaration(
        string $attributeName,
        string $attributeType,
        string $visibilityType,
        bool $nullable = false
    ): string {
        $getterFunctionName = 'get' . studly_case($attributeName);

        $attributeType = ($nullable ? '?' : '') . $attributeType;

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
     * @param bool $nullable Determines is returned value can be NULL
     *
     * @return string
     */
    protected function getDescription(string $attributeName, string $attributeType, bool $nullable = false): string
    {
        $attributeType .= $nullable ? '|null' : '';

        return <<<template
/**
 * Get {$attributeName} attribute value.
 *
 * @return {$attributeType}
 */
template;
    }
}
