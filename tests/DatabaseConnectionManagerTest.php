<?php

namespace Saritasa\LaravelTools\Tests;

use Doctrine\DBAL\Connection;
use Illuminate\Database\DatabaseManager;
use Saritasa\LaravelTools\Database\DatabaseConnectionManager;
use PHPUnit\Framework\TestCase;

/**
 * Test database connection manager
 */
class DatabaseConnectionManagerTest extends TestCase
{
    /**
     * Test that database connection manager return connection which we expected.
     *
     * @return void
     */
    public function testGetConnection()
    {
        $databaseManager = \Mockery::mock(DatabaseManager::class);
        $expectedConnection = \Mockery::mock(Connection::class);
        $databaseManager->shouldReceive('getDoctrineConnection')->withArgs([])->andReturn($expectedConnection);
        $databaseConnectionManager = new DatabaseConnectionManager($databaseManager);
        $actualConnection = $databaseConnectionManager->getConnection();
        $this->assertEquals($expectedConnection, $actualConnection);
    }
}
