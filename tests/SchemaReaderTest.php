<?php

namespace Saritasa\LaravelTools\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;
use Saritasa\LaravelTools\Database\SchemaReader;

/**
 * Test Schema reader.
 */
class SchemaReaderTest extends TestCase
{
    /**
     * Test that exception will thrown if doctrine has not registered platform.
     *
     * @throws DBALException
     *
     * @return void
     */
    public function testExceptionWillThrownIfPlatformNotSet()
    {
        $doctrineConnection = \Mockery::mock(Connection::class);
        $exception = new DBALException(str_random());

        $doctrineConnection
            ->shouldReceive('getDatabasePlatform')
            ->withArgs([])
            ->andThrow($exception);

        $this->expectExceptionObject($exception);
        /** @var Connection $doctrineConnection */
        new SchemaReader($doctrineConnection);
    }

    /**
     * Test that exception will thrown if doctrine has not enum type.
     *
     * @throws DBALException
     *
     * @return void
     */
    public function testExceptionWillThrownIfEnumTypeNotExists()
    {
        $doctrineConnection = \Mockery::mock(Connection::class);
        $platform = \Mockery::mock(AbstractPlatform::class);

        $exception = new DBALException(str_random());

        $doctrineConnection
            ->shouldReceive('getDatabasePlatform')
            ->withArgs([])
            ->andReturn($platform);

        $platform
            ->shouldReceive('registerDoctrineTypeMapping')
            ->withArgs(['enum', Type::STRING])
            ->andThrow($exception);

        $this->expectExceptionObject($exception);

        /** @var Connection $doctrineConnection */
        new SchemaReader($doctrineConnection);
    }

    /**
     * Test that table detail method return expected Table object.
     *
     * @throws DBALException
     *
     * @return void
     */
    public function testTableDetails()
    {
        $doctrineConnection = \Mockery::mock(Connection::class);
        $platform = \Mockery::mock(AbstractPlatform::class);
        $schemaManager = \Mockery::mock(AbstractSchemaManager::class);
        $expectedTable = \Mockery::mock(Table::class);
        $tableName = str_random();

        $doctrineConnection
            ->shouldReceive('getDatabasePlatform')
            ->withArgs([])
            ->andReturn($platform);
        $doctrineConnection
            ->shouldReceive('getSchemaManager')
            ->withArgs([])
            ->andReturn($schemaManager);

        $platform
            ->shouldReceive('registerDoctrineTypeMapping')
            ->withArgs(['enum', Type::STRING])
            ->andReturnNull();

        $schemaManager
            ->shouldReceive('listTableDetails')
            ->withArgs([$tableName])
            ->andReturn($expectedTable);

        /** @var Connection $doctrineConnection */
        $schemaReader = new SchemaReader($doctrineConnection);
        $actualTable = $schemaReader->getTableDetails($tableName);
        $this->assertEquals($expectedTable, $actualTable);
    }
}
