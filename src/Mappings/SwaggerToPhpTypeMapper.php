<?php

namespace Saritasa\LaravelTools\Mappings;

use Saritasa\Exceptions\NotImplementedException;
use Saritasa\LaravelTools\Enums\PhpMixedTypes;
use Saritasa\LaravelTools\Enums\PhpScalarTypes;
use Saritasa\LaravelTools\Enums\SwaggerTypes;

/**
 * Swagger type to PHP scalar type mapper.
 */
class SwaggerToPhpTypeMapper implements IPhpTypeMapper
{
    /**
     * Swagger to scalar PHP types map.
     *
     * @var array
     */
    private $typeMappings = [
        SwaggerTypes::ARRAY => PhpMixedTypes::ARRAY,
        SwaggerTypes::OBJECT => PhpMixedTypes::OBJECT,
        // Integer types
        SwaggerTypes::INTEGER => PhpScalarTypes::INTEGER,
        // Float types
        SwaggerTypes::NUMBER => PhpScalarTypes::FLOAT,
        // Boolean types
        SwaggerTypes::BOOLEAN => PhpScalarTypes::BOOLEAN,
        // String types
        SwaggerTypes::STRING => PhpScalarTypes::STRING,
    ];

    /**
     * Returns PHP scalar type representation of Swagger type.
     *
     * @param string $type Swagger type name
     *
     * @return string
     * @throws NotImplementedException
     */
    public function getPhpType(string $type): string
    {
        $phpType = $this->typeMappings[strtolower($type)] ?? null;

        if (is_null($phpType)) {
            throw new NotImplementedException("PHP scalar type mapping for Swagger type [{$type}] is not supported");
        }

        return $phpType;
    }
}
