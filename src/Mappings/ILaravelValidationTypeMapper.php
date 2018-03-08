<?php

namespace Saritasa\LaravelTools\Mappings;

/**
 * Storage-specific type to validation type mapper.
 */
interface ILaravelValidationTypeMapper
{
    /**
     * Returns validation type representation of storage-specific type .
     *
     * @param string $type Storage-specific type name
     *
     * @return string|null
     */
    public function getValidationType(string $type): ?string;
}
