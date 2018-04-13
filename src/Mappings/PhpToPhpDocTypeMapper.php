<?php

namespace Saritasa\LaravelTools\Mappings;

use Saritasa\LaravelTools\Enums\PhpDocScalarTypes;
use Saritasa\LaravelTools\Enums\PhpScalarTypes;

/**
 * Php scalar type to PhpDoc scalar type mapper.
 */
class PhpToPhpDocTypeMapper
{
    /**
     * Php scalar type to PhpDoc types map.
     *
     * @var array
     */
    private $typeMappings = [
        PhpScalarTypes::BOOLEAN => PhpDocScalarTypes::BOOLEAN,
        PhpScalarTypes::INTEGER => PhpDocScalarTypes::INTEGER,
        PhpScalarTypes::STRING => PhpDocScalarTypes::STRING,
        PhpScalarTypes::FLOAT => PhpDocScalarTypes::FLOAT,
    ];

    /**
     * Returns PhpDoc type representation of Php scalar type.
     *
     * @param string $type Php type name
     *
     * @return string
     */
    public function getPhpDocType(string $type): string
    {
        return $this->typeMappings[strtolower($type)] ?? $type;
    }
}
