<?php

namespace Saritasa\LaravelTools\Mappings;

use Saritasa\Exceptions\NotImplementedException;

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
     * @throws NotImplementedException
     */
    public function getPhpType(string $type): string;
}
