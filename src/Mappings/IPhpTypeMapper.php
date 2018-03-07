<?php

namespace Saritasa\LaravelTools\Mappings;

use RuntimeException;

/**
 * Storage-specific type to PHP scalar type mapper.
 */
interface IPhpTypeMapper
{
    /**
     * Returns PHP scalar type representation of storage type.
     *
     * @param string $type storage type name
     *
     * @return string
     * @throws RuntimeException
     */
    public function getPhpType(string $type): string;
}
