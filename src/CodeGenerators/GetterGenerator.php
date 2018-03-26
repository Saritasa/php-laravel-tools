<?php

namespace Saritasa\LaravelTools\CodeGenerators;

/**
 * Getter Generator class. Allows to generate getter function declaration for given attribute and type.
 */
class GetterGenerator
{
    /**
     * Allows to generate getter function declaration for given attribute and type.
     *
     * @param string $attributeName Attribute name for which need to generate getter
     * @param string $attributeType Attribute type to typehint getter
     *
     * @return string
     */
    public function render(string $attributeName, string $attributeType): string
    {
        return implode("\n", [
            $this->getDescription($attributeName, $attributeType),
            $this->getDeclaration($attributeName, $attributeType),
        ]);
    }

    /**
     * Returns getter function declaration.
     *
     * @param string $attributeName Attribute name for which need to generate getter
     * @param string $attributeType Attribute type to typehint getter
     *
     * @return string
     */
    protected function getDeclaration(string $attributeName, string $attributeType): string
    {
        $getterFunctionName = 'get' . ucfirst($attributeName);

        return <<<template
public function {$getterFunctionName}(): {$attributeType}
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
