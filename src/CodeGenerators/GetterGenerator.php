<?php

namespace Saritasa\LaravelTools\CodeGenerators;

/**
 * Getter Generator class. Allows to generate getter function code for given attribute and type.
 */
class GetterGenerator
{
    /**
     * Allows to generate getter function code for given attribute and type.
     *
     * @param string $attributeName Attribute name for which need to generate getter
     * @param string $valueType Attribute type to typehint getter
     *
     * @return string
     */
    public function render(string $attributeName, string $valueType): string
    {
        $getterFunctionName = 'get' . ucfirst($attributeName);

        $getterFunctionCode = <<<template
/**
 * Get {$attributeName} attribute value.
 *
 * @returns {$valueType}
 */
 public function {$getterFunctionName}(): {$valueType}
 {
     return \$this->{$attributeName};
 }
template;

        return $getterFunctionCode;
    }
}
