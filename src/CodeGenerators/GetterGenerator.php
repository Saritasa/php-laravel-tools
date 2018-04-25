<?php

namespace Saritasa\LaravelTools\CodeGenerators;

use Saritasa\LaravelTools\DTO\PhpClasses\FunctionObject;
use Saritasa\LaravelTools\Enums\ClassMemberVisibilityTypes;

/**
 * Getter Generator class. Allows to generate getter function declaration for given attribute and type.
 */
class GetterGenerator
{
    /**
     * Php function code generator. Allows to generate function declaration by parameters.
     *
     * @var FunctionGenerator
     */
    private $functionGenerator;

    /**
     * Getter Generator class. Allows to generate getter function declaration for given attribute and type.
     *
     * @param FunctionGenerator $functionGenerator Php function code generator. Allows to generate function declaration
     *     by parameters
     */
    public function __construct(FunctionGenerator $functionGenerator)
    {
        $this->functionGenerator = $functionGenerator;
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
        string $visibilityType = ClassMemberVisibilityTypes::PUBLIC,
        bool $nullable = false
    ): string {
        return $this->functionGenerator->render(new FunctionObject([
            FunctionObject::NAME => 'get' . studly_case($attributeName),
            FunctionObject::DESCRIPTION => "Get {$attributeName} attribute value",
            FunctionObject::RETURN_TYPE => $attributeType,
            FunctionObject::NULLABLE_RESULT => $nullable,
            FunctionObject::VISIBILITY_TYPE => $visibilityType,
            FunctionObject::CONTENT => "return \$this->{$attributeName};",
        ]));
    }
}
