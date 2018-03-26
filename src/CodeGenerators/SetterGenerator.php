<?php

namespace Saritasa\LaravelTools\CodeGenerators;

/**
 * Setter Generator class. Allows to generate setter function declaration for given attribute and type.
 */
class SetterGenerator
{
    /**
     * Allows to generate setter function declaration for given attribute and type.
     *
     * @param string $attributeName Attribute name for which need to generate setter
     * @param string $attributeType Attribute type to typehint value
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
     * Returns setter function declaration.
     *
     * @param string $attributeName Attribute name for which need to generate setter
     * @param string $attributeType Attribute type to typehint value
     *
     * @return string
     */
    protected function getDeclaration(string $attributeName, string $attributeType): string
    {
        $setterFunctionName = 'set' . ucfirst($attributeName);

        return <<<template
 public function {$setterFunctionName}({$attributeType} \${$attributeName}): void
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
