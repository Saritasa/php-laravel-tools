<?php

namespace Saritasa\LaravelTools\Tests;

use PHPUnit\Framework\TestCase;
use Saritasa\LaravelTools\DTO\ClassPropertyObject;
use Saritasa\LaravelTools\Enums\PhpDocPropertyAccessTypes;
use Saritasa\LaravelTools\Mappings\PhpToPhpDocTypeMapper;
use Saritasa\LaravelTools\PhpDoc\PhpDocClassDescriptionBuilder;
use Saritasa\LaravelTools\PhpDoc\PhpDocSingleLinePropertyDescriptionBuilder;
use Saritasa\LaravelTools\PhpDoc\PhpDocVariableDescriptionBuilder;

/**
 * Test PhpDoc block rendering function.
 */
class PhpDocBuilderTest extends TestCase
{
    /** @var PhpDocSingleLinePropertyDescriptionBuilder */
    private $phpDocPropertyBuilder;

    /** @var PhpDocClassDescriptionBuilder */
    private $phpDocClassDescriptionBuilder;

    /** @var PhpDocVariableDescriptionBuilder */
    private $phpDocVariableDescriptionBuilder;

    protected function setUp()
    {
        parent::setUp();
        $this->phpDocPropertyBuilder = new PhpDocSingleLinePropertyDescriptionBuilder(new PhpToPhpDocTypeMapper());
        $this->phpDocClassDescriptionBuilder = new PhpDocClassDescriptionBuilder($this->phpDocPropertyBuilder);
        $this->phpDocVariableDescriptionBuilder = new PhpDocVariableDescriptionBuilder(new PhpToPhpDocTypeMapper());
    }

    /**
     * Test class property line renderer function.
     *
     * @return void
     */
    public function testPropertyRenderFunction()
    {
        $classProperty = new ClassPropertyObject([
            ClassPropertyObject::NAME => 'variable',
            ClassPropertyObject::TYPE => 'string',
            ClassPropertyObject::NULLABLE => false,
            ClassPropertyObject::DESCRIPTION => 'Some description',
            ClassPropertyObject::ACCESS_TYPE => PhpDocPropertyAccessTypes::READ,
        ]);

        $renderedLine = $this->phpDocPropertyBuilder->render($classProperty);
        $this->assertEquals(' * @property-read string $variable Some description', $renderedLine);

        $classProperty->accessType = PhpDocPropertyAccessTypes::WRITE;
        $renderedLine = $this->phpDocPropertyBuilder->render($classProperty);
        $this->assertEquals(' * @property-write string $variable Some description', $renderedLine);

        $classProperty->accessType = PhpDocPropertyAccessTypes::READ_AND_WRITE;
        $renderedLine = $this->phpDocPropertyBuilder->render($classProperty);
        $this->assertEquals(' * @property string $variable Some description', $renderedLine);

        $classProperty->nullable = true;
        $renderedLine = $this->phpDocPropertyBuilder->render($classProperty);
        $this->assertEquals(' * @property string|null $variable Some description', $renderedLine);
    }

    /**
     * Test class description rendering function.
     *
     * @return void
     */
    public function testClassDescriptionRenderFunction()
    {
        // Test with class properties
        $classProperty = new ClassPropertyObject([
            ClassPropertyObject::NAME => 'variable',
            ClassPropertyObject::TYPE => 'string',
            ClassPropertyObject::NULLABLE => false,
            ClassPropertyObject::DESCRIPTION => 'Some description',
            ClassPropertyObject::ACCESS_TYPE => PhpDocPropertyAccessTypes::READ,
        ]);

        $classDescription = 'Some class';
        $classPhpDoc = $this->phpDocClassDescriptionBuilder->render($classDescription, [$classProperty]);

        $expectedPhpDoc = "/**\n" .
            " * Some class.\n" .
            " *\n" .
            " * @property-read string \$variable Some description\n" .
            " */";
        $this->assertEquals($expectedPhpDoc, $classPhpDoc);

        // Test without class properties
        $classPhpDoc = $this->phpDocClassDescriptionBuilder->render($classDescription, []);

        $expectedPhpDoc = "/**\n" .
            " * Some class.\n" .
            " */";
        $this->assertEquals($expectedPhpDoc, $classPhpDoc);
    }

    /**
     * Test variable description rendering function.
     *
     * @return void
     */
    public function testVariableDescriptionRenderFunction()
    {
        // Tet simple type
        $classProperty = new ClassPropertyObject([
            ClassPropertyObject::NAME => 'variable',
            ClassPropertyObject::TYPE => 'string',
            ClassPropertyObject::NULLABLE => false,
            ClassPropertyObject::DESCRIPTION => 'Some description',
            ClassPropertyObject::ACCESS_TYPE => PhpDocPropertyAccessTypes::READ,
        ]);

        $classPhpDoc = $this->phpDocVariableDescriptionBuilder->render($classProperty);

        $expectedPhpDoc = "/**\n" .
            " * Some description.\n" .
            " *\n" .
            " * @var string\n" .
            " */";
        $this->assertEquals($expectedPhpDoc, $classPhpDoc);

        // Test with nullable
        $classProperty->nullable = true;
        $classPhpDoc = $this->phpDocVariableDescriptionBuilder->render($classProperty);

        $expectedPhpDoc = "/**\n" .
            " * Some description.\n" .
            " *\n" .
            " * @var string|null\n" .
            " */";
        $this->assertEquals($expectedPhpDoc, $classPhpDoc);

        // Test with indent
        $classPhpDoc = $this->phpDocVariableDescriptionBuilder->render($classProperty);

        $expectedPhpDoc = "/**\n" .
            " * Some description.\n" .
            " *\n" .
            " * @var string|null\n" .
            " */";
        $this->assertEquals($expectedPhpDoc, $classPhpDoc);
    }
}
