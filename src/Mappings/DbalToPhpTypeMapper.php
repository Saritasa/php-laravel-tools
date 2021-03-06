<?php

namespace Saritasa\LaravelTools\Mappings;

use Doctrine\DBAL\Types\Type;
use Saritasa\Exceptions\NotImplementedException;
use Saritasa\LaravelTools\Enums\PhpScalarTypes;

/**
 * DBAL type to PHP scalar type mapper.
 */
class DbalToPhpTypeMapper implements IPhpTypeMapper
{
    /**
     * DBAL to scalar PHP types map.
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
        Type::BIGINT => PhpScalarTypes::INTEGER,
        Type::DECIMAL => PhpScalarTypes::INTEGER,
        Type::INTEGER => PhpScalarTypes::INTEGER,
        Type::SMALLINT => PhpScalarTypes::INTEGER,
        // Float types
        Type::FLOAT => PhpScalarTypes::FLOAT,
        // Boolean types
        Type::BOOLEAN => PhpScalarTypes::BOOLEAN,
        // String types
        Type::DATETIME => PhpScalarTypes::STRING,
        Type::DATETIME_IMMUTABLE => PhpScalarTypes::STRING,
        Type::DATETIMETZ => PhpScalarTypes::STRING,
        Type::DATETIMETZ_IMMUTABLE => PhpScalarTypes::STRING,
        Type::DATE => PhpScalarTypes::STRING,
        Type::DATE_IMMUTABLE => PhpScalarTypes::STRING,
        Type::TIME => PhpScalarTypes::STRING,
        Type::TIME_IMMUTABLE => PhpScalarTypes::STRING,
        Type::OBJECT => PhpScalarTypes::STRING,
        Type::STRING => PhpScalarTypes::STRING,
        Type::TEXT => PhpScalarTypes::STRING,
        Type::BINARY => PhpScalarTypes::STRING,
        Type::BLOB => PhpScalarTypes::STRING,
        Type::GUID => PhpScalarTypes::STRING,
        Type::DATEINTERVAL => PhpScalarTypes::STRING,
    ];

    /**
     * Returns PHP scalar type representation of DBAL type.
     *
     * @param string $type DBAL type name
     *
     * @return string
     * @throws NotImplementedException
     */
    public function getPhpType(string $type): string
    {
        $phpType = $this->typeMappings[strtolower($type)] ?? null;

        if (is_null($phpType)) {
            throw new NotImplementedException("PHP scalar type mapping for DBAL type [{$type}] is not supported");
        }

        return $phpType;
    }
}
