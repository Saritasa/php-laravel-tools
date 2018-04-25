<?php

namespace Saritasa\LaravelTools\CodeGenerators;

use Saritasa\LaravelTools\DTO\PhpClasses\FunctionObject;
use Saritasa\LaravelTools\DTO\PhpClasses\FunctionParameterObject;
use Saritasa\LaravelTools\Enums\ClassMemberVisibilityTypes;
use Saritasa\LaravelTools\Enums\PhpPseudoTypes;

/**
 * Setter Generator class. Allows to generate setter function declaration for given attribute and type.
 */
class SetterGenerator
{
    /**
     * Php function code generator. Allows to generate function declaration by parameters.
     *
     * @var FunctionGenerator
     */
    private $functionGenerator;

    /**
     * Setter Generator class. Allows to generate setter function declaration for given attribute and type.
     *
     * @param FunctionGenerator $functionGenerator Php function code generator. Allows to generate function declaration
     *     by parameters
     */
    public function __construct(FunctionGenerator $functionGenerator)
    {
        $this->functionGenerator = $functionGenerator;
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
        string $visibilityType = ClassMemberVisibilityTypes::PUBLIC,
        bool $nullable = false
    ): string {
        return $this->functionGenerator->render(new FunctionObject([
            FunctionObject::NAME => 'set' . studly_case($attributeName),
            FunctionObject::DESCRIPTION => "Set {$attributeName} attribute value",
            FunctionObject::RETURN_TYPE => PhpPseudoTypes::VOID,
            FunctionObject::VISIBILITY_TYPE => $visibilityType,
            FunctionObject::CONTENT => "\$this->{$attributeName} = \${$attributeName};",
            FunctionObject::PARAMETERS => [
                new FunctionParameterObject([
                    FunctionParameterObject::NAME => $attributeName,
                    FunctionParameterObject::DESCRIPTION => 'New attribute value',
                    FunctionParameterObject::TYPE => $attributeType,
                    FunctionParameterObject::NULLABLE => $nullable,
                ]),
            ],
        ]));
    }
}
