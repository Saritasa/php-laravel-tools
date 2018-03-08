<?php

namespace Saritasa\LaravelTools\Tests;

use PHPUnit\Framework\TestCase;
use Saritasa\LaravelTools\DTO\ClassPropertyObject;
use Saritasa\LaravelTools\Enums\PhpDocPropertyAccessTypes;
use Saritasa\LaravelTools\PhpDoc\PhpDocClassDescriptionBuilder;
use Saritasa\LaravelTools\PhpDoc\PhpDocPropertyBuilder;

/**
 * Test PhpDoc block rendering function.
 */
class PhpDocBuilderTest extends TestCase
{
    /** @var PhpDocPropertyBuilder */
    private $phpDocPropertyBuilder;

    /** @var PhpDocClassDescriptionBuilder */
    private $phpDocClassDescriptionBuilder;

    protected function setUp()
    {
        parent::setUp();
        $this->phpDocPropertyBuilder = new PhpDocPropertyBuilder();
        $this->phpDocClassDescriptionBuilder = new PhpDocClassDescriptionBuilder($this->phpDocPropertyBuilder);
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
    }
}
