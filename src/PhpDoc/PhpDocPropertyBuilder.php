<?php

namespace Saritasa\LaravelTools\PhpDoc;

use Saritasa\LaravelTools\DTO\ClassPropertyObject;
use Saritasa\LaravelTools\Enums\PhpDocPropertyAccessTypes;

/**
 * PhpDoc property line builder. Allows to generate PhpDoc property line with variable type and description.
 */
class PhpDocPropertyBuilder
{
    /**
     * Return PhpDoc property line description.
     *
     * @param ClassPropertyObject $classProperty Class property details
     *
     * @return string
     * @see PhpDocPropertyAccessTypes for available propety access types details
     */
    public function render(ClassPropertyObject $classProperty): string
    {
        $nullableType = $classProperty->nullable ? '|null' : '';
        switch ($classProperty->access_type) {
            case PhpDocPropertyAccessTypes::READ:
                $accessModifier = '-read';
                break;
            case PhpDocPropertyAccessTypes::WRITE:
                $accessModifier = '-write';
                break;
            default:
                $accessModifier = '';
                break;
        }

        return trim(
            " * @property{$accessModifier} " .
            "{$classProperty->type}{$nullableType} \${$classProperty->name} " .
            "{$classProperty->description}"
        );
    }
}
