<?php

namespace Saritasa\LaravelTools\Database;

use Doctrine\DBAL\Connection;
use Illuminate\Database\DatabaseManager;

/**
 * Database connection manager. Allows to retrieve database connection.
 */
class DatabaseConnectionManager
{
    /**
     * Database connection manager.
     *
     * @var DatabaseManager $databaseManager Application's database connection manager
     */
    private $databaseManager;

    /**
     * Database connection manager. Allows to retrieve database connection.
     *
     * @param DatabaseManager $databaseManager Application's database connection manager
     */
    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * Returns doctrine database connection.
     *
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->databaseManager->getDoctrineConnection();
    }
}
