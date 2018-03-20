<?php

namespace Saritasa\LaravelTools\PhpDoc;

use Saritasa\LaravelTools\DTO\ClassPropertyObject;

/**
 * PhpDoc for variable builder. Allows to generate PhpDoc variable description block.
 */
class PhpDocVariableDescriptionBuilder
{
    /**
     * Return PhpDoc variable description block.
     *
     * @param ClassPropertyObject $classProperty Class property details
     * @param string $indent Description indent
     *
     * @return string
     */
    public function render(ClassPropertyObject $classProperty, string $indent = ''): string
    {
        $nullableType = $classProperty->nullable
            ? '|null'
            : '';

        return "{$indent}/**\n" .
            "{$indent} * {$classProperty->description}.\n" .
            "{$indent} *\n" .
            "{$indent} * @var {$classProperty->type}{$nullableType}\n" .
            "{$indent} */";
    }
}
