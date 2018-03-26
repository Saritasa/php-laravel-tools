<?php

namespace Saritasa\LaravelTools\CodeGenerators;

/**
 * Setter Generator class. Allows to generate setter function code for given attribute and type.
 */
class SetterGenerator
{
    /**
     * Allows to generate setter function code for given attribute and type.
     *
     * @param string $attributeName Attribute name for which need to generate setter
     * @param string $valueType Attribute type to typehint value
     *
     * @return string
     */
    public function render(string $attributeName, string $valueType): string
    {
        $setterFunctionName = 'set' . ucfirst($attributeName);

        $setterFunctionCode = <<<template
/**
 * Set {$attributeName} attribute value.
 *
 * @returns void
 */
 public function {$setterFunctionName}({$valueType} \${$attributeName}): void
 {
     \$this->{$attributeName} = \${$attributeName};
 }
template;

        return $setterFunctionCode;
    }
}
