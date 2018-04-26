<?php

namespace Saritasa\LaravelTools\Tests;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;
use Saritasa\LaravelTools\Mappings\DbalToLaravelValidationTypeMapper;
use Saritasa\LaravelTools\Rules\RuleBuilder;
use Saritasa\LaravelTools\Rules\StringValidationRulesDictionary;

/**
 * Test validation rules builder.
 */
class RuleBuilderTest extends TestCase
{
    /** @var RuleBuilder */
    private $ruleBuilder;

    protected function setUp()
    {
        parent::setUp();
        $this->ruleBuilder = new RuleBuilder(
            new StringValidationRulesDictionary(),
            new DbalToLaravelValidationTypeMapper()
        );
    }

    /**
     * Test that rule builder builds valid rule set for given column.
     *
     * @return void
     * @throws DBALException
     */
    public function testRuleBuilder()
    {
        // Test required string
        $column = new Column('some_column_name', Type::getType(Type::STRING), []);
        $actual = $this->ruleBuilder->generateRules($column, null);
        $expected = '\'required|string\'';
        $this->assertEquals($expected, $actual);

        // Test nullable
        $column->setNotnull(false);
        $actual = $this->ruleBuilder->generateRules($column, null);
        $expected = '\'nullable|string\'';
        $this->assertEquals($expected, $actual);

        // Test string max length
        $column->setLength(10);
        $actual = $this->ruleBuilder->generateRules($column, null);
        $expected = '\'nullable|string|max:10\'';
        $this->assertEquals($expected, $actual);

        // Test foreign key constraint
        $foreignKeyConstraint = new ForeignKeyConstraint(['user_id'], 'users', ['id']);
        $column->setType(Type::getType(Type::BIGINT));
        $actual = $this->ruleBuilder->generateRules($column, $foreignKeyConstraint);
        $expected = '\'nullable|exists:users,id|integer\'';
        $this->assertEquals($expected, $actual);

        // Test types
        $typesToTest = [
            Type::FLOAT => 'numeric',
            Type::BOOLEAN => 'boolean',
            Type::DATETIME => 'date',
            Type::TARRAY => null,
        ];
        foreach ($typesToTest as $columnType => $expectedRuleType) {
            $column->setType(Type::getType($columnType));
            $actual = $this->ruleBuilder->generateRules($column, null);
            if (is_null($expectedRuleType)) {
                $expected = "'nullable'";
            } else {
                $expected = "'nullable|{$expectedRuleType}'";
            }
            $this->assertEquals($expected, $actual);
        }
    }
}
