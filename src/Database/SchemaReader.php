<?php

namespace Saritasa\LaravelTools\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;

/**
 * Database schema reader. Allows to retrieve table details.
 */
class SchemaReader
{
    /**
     * Connection to retrieve information from.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Database schema reader. Allows to retrieve table details.
     *
     * @var AbstractSchemaManager
     */
    private $schemaManager;

    /**
     * Database schema reader. Allows to retrieve table details.
     *
     * @param Connection $connection Connection to retrieve information from
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->schemaManager = $connection->getSchemaManager();
    }

    /**
     * Retrieves information about columns in table.
     *
     * @param string $table Table name to retrieve information from
     *
     * @return Column[]|array
     */
    public function getColumnsDetails(string $table): array
    {
        return $this->schemaManager->listTableColumns($table);
    }
}
