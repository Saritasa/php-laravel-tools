<?php

namespace Saritasa\LaravelTools\Tests;

use PHPUnit\Framework\TestCase;
use Saritasa\LaravelTools\CodeGenerators\GetterGenerator;
use Saritasa\LaravelTools\CodeGenerators\SetterGenerator;

/**
 * Test Code generators block rendering function.
 */
class CodeGeneratorsTest extends TestCase
{
    /**
     * Generator of getter-function declaration.
     *
     * @var GetterGenerator
     */
    private $getterGenerator;

    /**
     * Generator of setter-function declaration.
     *
     * @var SetterGenerator
     */
    private $setterGenerator;

    protected function setUp()
    {
        parent::setUp();
        $this->getterGenerator = new GetterGenerator();
        $this->setterGenerator = new SetterGenerator();
    }

    /**
     * Test setter function generation.
     *
     * @return void
     */
    public function testSetter()
    {
        $expected = <<<EXPECTED
/**
 * Set first_name attribute value.
 *
 * @param string \$first_name New attribute value
 *
 * @return void
 */
public function setFirst_name(string \$first_name): void
{
    \$this->first_name = \$first_name;
}
EXPECTED;

        $actual = $this->setterGenerator->render('first_name', 'string');

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test getter function generation.
     *
     * @return void
     */
    public function testGetter()
    {
        $expected = <<<EXPECTED
/**
 * Get first_name attribute value.
 *
 * @return string
 */
public function getFirst_name(): string
{
    return \$this->first_name;
}
EXPECTED;

        $actual = $this->getterGenerator->render('first_name', 'string');

        $this->assertEquals($expected, $actual);
    }
}
