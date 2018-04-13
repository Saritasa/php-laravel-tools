<?php

namespace Saritasa\LaravelTools\Tests;

use Illuminate\Config\Repository;
use PHPUnit\Framework\TestCase;
use Saritasa\LaravelTools\CodeGenerators\CodeStyler;

class CodeStylerTest extends TestCase
{
    /**
     * Configured code styler that will be tested.
     *
     * @var CodeStyler
     */
    private $codeStyler;

    public function setUp()
    {
        parent::setUp();
        $configsRepository = new Repository(['laravel_tools.code_style.indent' => '    ']);
        $this->codeStyler = new CodeStyler($configsRepository);
    }

    /**
     *
     * @dataProvider lineIndentTestSet
     *
     * @param string $line Line that should be indented
     * @param bool $ignoreExisting Ignore existing indents in line
     * @param int $indentSize How many indents
     * @param string $expected What result is expected
     *
     * @return void
     */
    public function testLineIndent(string $line, bool $ignoreExisting, int $indentSize, string $expected): void
    {
        $actual = $this->codeStyler->indentLine($line, $ignoreExisting, $indentSize);

        $this->assertEquals($expected, $actual);
    }

    public function lineIndentTestSet(): array
    {
        return [
            'one indent' => ['$x = $y;', false, 1, '    $x = $y;'],
            'three indents' => ['$x = $y;', false, 3, '            $x = $y;'],
            'ignored indents with two indents' => ['    $x = $y;', true, 2, '        $x = $y;'],
            'one additional indent' => ['    $x = $y;', false, 1, '        $x = $y;'],
        ];
    }

    /**
     *
     * @dataProvider blockIndentTestSet
     *
     * @param string $block Block that should be indented
     * @param bool $ignoreExisting Ignore existing indents in block
     * @param int $indentSize How many indents
     * @param string $expected What result is expected
     *
     * @return void
     */
    public function testBlockIndent(string $block, bool $ignoreExisting, int $indentSize, string $expected): void
    {
        $actual = $this->codeStyler->indentBlock($block, $ignoreExisting, $indentSize);

        $this->assertEquals($expected, $actual);
    }

    public function blockIndentTestSet(): array
    {
        return [
            'one indent' => ["\$x = \$y;\n\$a = \$b;", false, 1, "    \$x = \$y;\n    \$a = \$b;"],
            'three indents' => ["\$x = \$y;\n\$a = \$b;", false, 3, "            \$x = \$y;\n            \$a = \$b;"],
            'ignored indents with two indents' => ["    \$x = \$y;\n    \$a = \$b;", true, 2, "        \$x = \$y;\n        \$a = \$b;"],
            'one additional indent' => ["    \$x = \$y;\n    \$a = \$b;", false, 1, "        \$x = \$y;\n        \$a = \$b;"],
        ];
    }
}