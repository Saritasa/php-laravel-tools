<?php

namespace Saritasa\LaravelTools\Tests;

use Saritasa\LaravelTools\CodeGenerators\CodeFormatter;
use Saritasa\LaravelTools\CodeGenerators\CommentsGenerator;
use Saritasa\LaravelTools\CodeGenerators\FunctionGenerator;
use Saritasa\LaravelTools\CodeGenerators\PhpDoc\PhpDocMethodParameterDescriptionBuilder;
use Saritasa\LaravelTools\DTO\PhpClasses\FunctionObject;
use Saritasa\LaravelTools\DTO\PhpClasses\MethodParameterObject;
use Saritasa\LaravelTools\Enums\ClassMemberVisibilityTypes;
use Saritasa\LaravelTools\Enums\PhpScalarTypes;
use Saritasa\LaravelTools\Mappings\PhpToPhpDocTypeMapper;

class FunctionGeneratorTest extends LaravelToolsTestsHelpers
{
    public function testSetter(): void
    {
        $functionGenerator = new FunctionGenerator(
            new CodeFormatter($this->getConfigRepository()),
            new CommentsGenerator(),
            new PhpToPhpDocTypeMapper(),
            new PhpDocMethodParameterDescriptionBuilder(new PhpToPhpDocTypeMapper())
        );

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
                new MethodParameterObject([
                    MethodParameterObject::DESCRIPTION => 'First number with reach details',
                    MethodParameterObject::NAME => 'a',
                    MethodParameterObject::TYPE => PhpScalarTypes::INTEGER,
                    MethodParameterObject::NULLABLE => true,
                    MethodParameterObject::DEFAULT => 100,
                ]),
                new MethodParameterObject([
                    MethodParameterObject::DESCRIPTION => 'Simple second parameter',
                    MethodParameterObject::NAME => 'b',
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
