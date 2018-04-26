<?php

namespace Saritasa\LaravelTools\Mappings;

use RuntimeException;
use Saritasa\LaravelTools\Enums\PhpDocTypes;
use Saritasa\LaravelTools\Enums\PhpMixedTypes;
use Saritasa\LaravelTools\Enums\PhpPseudoTypes;
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
        // Scalar types mapping
        PhpScalarTypes::BOOLEAN => PhpDocTypes::BOOLEAN,
        PhpScalarTypes::INTEGER => PhpDocTypes::INTEGER,
        PhpScalarTypes::STRING => PhpDocTypes::STRING,
        PhpScalarTypes::FLOAT => PhpDocTypes::FLOAT,
        // Pseudo types mapping
        PhpPseudoTypes::VOID => PhpDocTypes::VOID,
        PhpPseudoTypes::MIXED => PhpDocTypes::MIXED,
        // Mixed types mapping
        PhpMixedTypes::OBJECT => PhpDocTypes::OBJECT,
        PhpMixedTypes::ARRAY => PhpDocTypes::ARRAY,
    ];

    /**
     * Returns PhpDoc type representation of Php scalar type.
     *
     * @param string|null $type Php type name
     *
     * @return string|null
     * @throws RuntimeException
     */
    public function getPhpDocType(?string $type): ?string
    {
        $phpType = $this->typeMappings[strtolower($type)] ?? null;

        if (is_null($phpType)) {
            return $type;
        }

        return $phpType;
    }
}
