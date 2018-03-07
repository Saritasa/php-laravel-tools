<?php

namespace Saritasa\LaravelTools\Mappings;

use Doctrine\DBAL\Types\Type;
use Saritasa\LaravelTools\Enums\LaravelValidationTypes;

/**
 * DBAL type to validation type mapper.
 */
class DbalToLaravelValidationTypeMapper implements ILaravelValidationTypeMapper
{
    /**
     * DBAL to laravel validation types map.
     *
     * @var array
     */
    private $typeMappings = [
        // Not supported types
        Type::TARRAY => null,
        Type::SIMPLE_ARRAY => null,
        Type::JSON_ARRAY => null,
        Type::JSON => null,
        // Integer types
        Type::BIGINT => LaravelValidationTypes::INTEGER,
        Type::DECIMAL => LaravelValidationTypes::INTEGER,
        Type::INTEGER => LaravelValidationTypes::INTEGER,
        Type::SMALLINT => LaravelValidationTypes::INTEGER,
        // Float types
        Type::FLOAT => LaravelValidationTypes::NUMERIC,
        // Boolean types
        Type::BOOLEAN => LaravelValidationTypes::BOOLEAN,
        // Date types
        Type::DATETIME => LaravelValidationTypes::DATE,
        Type::DATETIME_IMMUTABLE => LaravelValidationTypes::DATE,
        Type::DATETIMETZ => LaravelValidationTypes::DATE,
        Type::DATETIMETZ_IMMUTABLE => LaravelValidationTypes::DATE,
        Type::DATE => LaravelValidationTypes::DATE,
        Type::DATE_IMMUTABLE => LaravelValidationTypes::DATE,
        Type::TIME => LaravelValidationTypes::DATE,
        Type::TIME_IMMUTABLE => LaravelValidationTypes::DATE,
        // String types
        Type::OBJECT => LaravelValidationTypes::STRING,
        Type::STRING => LaravelValidationTypes::STRING,
        Type::TEXT => LaravelValidationTypes::STRING,
        Type::BINARY => LaravelValidationTypes::STRING,
        Type::BLOB => LaravelValidationTypes::STRING,
        Type::GUID => LaravelValidationTypes::STRING,
        Type::DATEINTERVAL => LaravelValidationTypes::STRING,
    ];

    /**
     * Returns validation type representation of DBAL type.
     *
     * @param string $type DBAL type name
     *
     * @return string
     */
    public function getValidationType(string $type): string
    {
        return $this->typeMappings[$type] ?? null;
    }
}
