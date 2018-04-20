<?php

namespace Saritasa\LaravelTools\Tests;

use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;
use Saritasa\Exceptions\NotImplementedException;
use Saritasa\LaravelTools\Mappings\DbalToLaravelValidationTypeMapper;
use Saritasa\LaravelTools\Mappings\DbalToPhpTypeMapper;

/**
 * Test dbal-types to laravel validation type and php scalar types mappings.
 */
class DbalMappersTest extends TestCase
{
    /** @var DbalToPhpTypeMapper */
    private $dbalToPhpTypeMapper;

    /** @var DbalToLaravelValidationTypeMapper */
    private $dbalToLaravelValidationTypeMapper;

    protected function setUp()
    {
        parent::setUp();
        $this->dbalToPhpTypeMapper = new DbalToPhpTypeMapper();
        $this->dbalToLaravelValidationTypeMapper = new DbalToLaravelValidationTypeMapper();
    }

    /**
     * Test that DBAL type to php scalar mappings work.
     *
     * @return void
     * @throws NotImplementedException
     */
    public function testDbalToPhpTypeMappings()
    {
        $phpType = $this->dbalToPhpTypeMapper->getPhpType(Type::TEXT);

        $this->assertEquals('string', $phpType);
    }

    /**
     * Test that DBAL type to php scalar mappings doesn't work with unsupported types.
     *
     * @return void
     * @throws NotImplementedException
     */
    public function testWrongDbalToPhpTypeMappings()
    {
        $this->expectException(NotImplementedException::class);

        $this->dbalToPhpTypeMapper->getPhpType('some_not_existing_type');
    }

    /**
     * Test that DBAL type to laravel validation type mappings work.
     *
     * @return void
     */
    public function testDbalToLaravelValidationTypeMapping()
    {
        $validationType = $this->dbalToLaravelValidationTypeMapper->getValidationType(Type::DATE);

        $this->assertEquals('date', $validationType);
    }

    /**
     * Test that DBAL type to laravel validation type returns empty result for unsupported types.
     *
     * @return void
     */
    public function testWrongDbalToLaravelValidationTypeMapping()
    {
        $validationType = $this->dbalToLaravelValidationTypeMapper->getValidationType('some_not_existing_type');

        $this->assertNull($validationType);
    }
}
