<?php

namespace Saritasa\LaravelTools\Tests;

use Saritasa\LaravelTools\DTO\PhpClasses\FunctionObject;
use Saritasa\LaravelTools\DTO\PhpClasses\FunctionParameterObject;
use Saritasa\LaravelTools\Enums\ClassMemberVisibilityTypes;
use Saritasa\LaravelTools\Enums\PhpScalarTypes;

class FunctionGeneratorTest extends LaravelToolsTestsHelpers
{
    public function testRender(): void
    {
        $functionGenerator = $this->getFunctionGenerator();

        // Simple function
        $functionObject = new FunctionObject([
            FunctionObject::NAME => 'index',
            FunctionObject::DESCRIPTION => 'Returns list of users',
            FunctionObject::VISIBILITY_TYPE => ClassMemberVisibilityTypes::PUBLIC,
        ]);

        $expected = <<<EXPECTED
/**
 * Returns list of users.
 */
public function index()
{
    // Do not forget to fill function body
}
EXPECTED;

        $actual = $functionGenerator->render($functionObject);

        $this->assertEquals($expected, $actual);

        // Simple with return type
        $functionObject = new FunctionObject([
            FunctionObject::NAME => 'index',
            FunctionObject::DESCRIPTION => 'Returns list of users',
            FunctionObject::VISIBILITY_TYPE => ClassMemberVisibilityTypes::PUBLIC,
            FunctionObject::RETURN_TYPE => 'Response',
        ]);

        $expected = <<<EXPECTED
/**
 * Returns list of users.
 *
 * @return Response
 */
public function index(): Response
{
    // Do not forget to fill function body
}
EXPECTED;

        $actual = $functionGenerator->render($functionObject);

        $this->assertEquals($expected, $actual);

        // Simple with nullable return type
        $functionObject = new FunctionObject([
            FunctionObject::NAME => 'index',
            FunctionObject::DESCRIPTION => 'Returns list of users',
            FunctionObject::VISIBILITY_TYPE => ClassMemberVisibilityTypes::PUBLIC,
            FunctionObject::RETURN_TYPE => 'Response',
            FunctionObject::NULLABLE_RESULT => true,
        ]);

        $expected = <<<EXPECTED
/**
 * Returns list of users.
 *
 * @return Response|null
 */
public function index(): ?Response
{
    // Do not forget to fill function body
}
EXPECTED;

        $actual = $functionGenerator->render($functionObject);

        $this->assertEquals($expected, $actual);

        // Simple with return type and body
        $functionObject = new FunctionObject([
            FunctionObject::NAME => 'booleanValues',
            FunctionObject::DESCRIPTION => 'Returns list of possible boolean values',
            FunctionObject::VISIBILITY_TYPE => ClassMemberVisibilityTypes::PUBLIC,
            FunctionObject::RETURN_TYPE => 'array',
            FunctionObject::CONTENT => 'return [true, false];',
        ]);

        $expected = <<<EXPECTED
/**
 * Returns list of possible boolean values.
 *
 * @return array
 */
public function booleanValues(): array
{
    return [true, false];
}
EXPECTED;

        $actual = $functionGenerator->render($functionObject);

        $this->assertEquals($expected, $actual);

        // With parameters
        $functionObject = new FunctionObject([
            FunctionObject::NAME => 'sum',
            FunctionObject::DESCRIPTION => 'Returns sum of passed values',
            FunctionObject::VISIBILITY_TYPE => ClassMemberVisibilityTypes::PUBLIC,
            FunctionObject::RETURN_TYPE => PhpScalarTypes::INTEGER,
            FunctionObject::CONTENT => 'return $a + $b;',
            FunctionObject::PARAMETERS => [
                new FunctionParameterObject([
                    FunctionParameterObject::DESCRIPTION => 'First number with reach details',
                    FunctionParameterObject::NAME => 'a',
                    FunctionParameterObject::TYPE => PhpScalarTypes::INTEGER,
                    FunctionParameterObject::NULLABLE => true,
                    FunctionParameterObject::DEFAULT => 100,
                ]),
                new FunctionParameterObject([
                    FunctionParameterObject::DESCRIPTION => 'Simple second parameter',
                    FunctionParameterObject::NAME => 'b',
                ]),
            ],
        ]);

        $expected = <<<EXPECTED
/**
 * Returns sum of passed values.
 *
 * @param integer|null \$a First number with reach details
 * @param \$b Simple second parameter
 *
 * @return integer
 */
public function sum(?int \$a = 100, \$b): int
{
    return \$a + \$b;
}
EXPECTED;

        $actual = $functionGenerator->render($functionObject);

        $this->assertEquals($expected, $actual);
    }
}
