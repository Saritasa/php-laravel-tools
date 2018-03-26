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
     *
     * @return string
     */
    public function render(ClassPropertyObject $classProperty): string
    {
        $nullableType = $classProperty->nullable
            ? '|null'
            : '';

        return <<<DESCRIPTION
/**
 * {$classProperty->description}.
 *
 * @var {$classProperty->type}{$nullableType}
 */
DESCRIPTION;
    }
}
