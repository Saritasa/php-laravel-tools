<?php

namespace Saritasa\LaravelTools\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

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
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;

        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', Type::STRING);

        $this->schemaManager = $connection->getSchemaManager();
    }

    /**
     * Retrieves information about table details.
     *
     * @param string $table Table name to retrieve information from
     *
     * @return Table
     */
    public function getTableDetails(string $table): Table
    {
        return $this->schemaManager->listTableDetails($table);
    }
}
