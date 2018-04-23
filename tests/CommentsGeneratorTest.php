<?php

namespace Saritasa\LaravelTools\Tests;

use PHPUnit\Framework\TestCase;
use Saritasa\LaravelTools\CodeGenerators\CommentsGenerator;
use Saritasa\LaravelTools\CodeGenerators\GetterGenerator;
use Saritasa\LaravelTools\CodeGenerators\SetterGenerator;
use Saritasa\LaravelTools\Mappings\PhpToPhpDocTypeMapper;

/**
 * Test comment generation class.
 */
class CommentsGeneratorTest extends TestCase
{
    /**
     * Php comments generator.
     *
     * @var CommentsGenerator
     */
    private $commentsGenerator;

    protected function setUp()
    {
        parent::setUp();
        $this->commentsGenerator = new CommentsGenerator();
    }

    public function testLine():void
    {
        $expected = '// Simple text to comment';
        $actual = $this->commentsGenerator->line('Simple text to comment');
        $this->assertEquals($expected, $actual);

        $expected = '// Simple text to comment without unnecessary spaces';
        $actual = $this->commentsGenerator->line('   Simple text to comment without unnecessary spaces   ');
        $this->assertEquals($expected, $actual);
    }

    public function testBlock():void
    {
        $expected = <<<BLOCK
/**
 * Simple
 * multi
 * line
 * block
 */
BLOCK;
        $actual = $this->commentsGenerator->block("Simple\nmulti\nline\nblock");
        $this->assertEquals($expected, $actual);

        $expected = <<<BLOCK
/**
 * Simple
 * multi
 * line
 * block
 * without unnecessary spaces
 */
BLOCK;
        $actual = $this->commentsGenerator->block("Simple\nmulti\nline\nblock\n   without unnecessary spaces   ");
        $this->assertEquals($expected, $actual);
    }

    public function testAlternativeBlock():void
    {
        $expected = <<<BLOCK
////////////
// Simple //
// block  //
////////////
BLOCK;
        $actual = $this->commentsGenerator->alternativeBlock("Simple\nblock");
        $this->assertEquals($expected, $actual);

        $expected = <<<BLOCK
///////////////
// Simple    //
// block     //
// no spaces //
///////////////
BLOCK;
        $actual = $this->commentsGenerator->alternativeBlock("Simple\nblock\n   no spaces   ");
        $this->assertEquals($expected, $actual);
    }
}
