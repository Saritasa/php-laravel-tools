<?php

namespace Saritasa\LaravelTools\Tests;

use Illuminate\Config\Repository;
use PHPUnit\Framework\TestCase;
use Saritasa\LaravelTools\CodeGenerators\CodeFormatter;

class CodeFormatterTest extends TestCase
{
    /**
     * Configured code formatter that will be tested.
     *
     * @var CodeFormatter
     */
    private $codeFormatter;

    public function setUp()
    {
        parent::setUp();
        $configsRepository = new Repository(['laravel_tools.code_style.indent' => '    ']);
        $this->codeFormatter = new CodeFormatter($configsRepository);
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
        $actual = $this->codeFormatter->indentLine($line, $ignoreExisting, $indentSize);

        $this->assertEquals($expected, $actual);
    }

    public function testEmptyStringIndenting(): void
    {
        $string = '';
        $expected = '';
        $actual = $this->codeFormatter->indentLine($string);
        $this->assertEquals($expected, $actual, 'Code formatter should not apply indent to empty lines');
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
        $actual = $this->codeFormatter->indentBlock($block, $ignoreExisting, $indentSize);

        $this->assertEquals($expected, $actual);
    }

    public function blockIndentTestSet(): array
    {
        return [
            'one indent' => ["\$x = \$y;\n\$a = \$b;", false, 1, "    \$x = \$y;\n    \$a = \$b;"],
            'three indents' => ["\$x = \$y;\n\$a = \$b;", false, 3, "            \$x = \$y;\n            \$a = \$b;"],
            'ignored indents with two indents' => [
                "    \$x = \$y;\n    \$a = \$b;",
                true,
                2,
                "        \$x = \$y;\n        \$a = \$b;",
            ],
            'one additional indent' => [
                "    \$x = \$y;\n    \$a = \$b;",
                false,
                1,
                "        \$x = \$y;\n        \$a = \$b;",
            ],
        ];
    }

    /**
     * @dataProvider toSentenceTestSet
     *
     * @param string $text Text to format as a sentence
     * @param boolean $dotEnded Should formatted sentence be dot ended or not
     * @param string $expected Expected result
     *
     * @return void
     */
    public function testToSentence(string $text, bool $dotEnded, string $expected)
    {
        $actual = $this->codeFormatter->toSentence($text, $dotEnded);
        $this->assertEquals($expected, $actual);
    }

    public function toSentenceTestSet(): array
    {
        return [
            'empty string' => ['', false, ''],
            'empty string' => ['', true, ''],
            'single space' => [' ', false, ''],
            'single space' => [' ', true, ''],
            'dot' => ['.', false, ''],
            'dot' => ['.', true, ''],
            ['simple sentence', false, 'Simple sentence'],
            ['simple dot ended sentence', true, 'Simple dot ended sentence.'],
            ['simple dot ended already dot ended sentence', true, 'Simple dot ended already dot ended sentence.'],
            [' with spaces around ', false, 'With spaces around'],
            [' with spaces around dot ended ', true, 'With spaces around dot ended.'],
        ];
    }

    /**
     * @dataProvider anyCaseToWordsTestSet
     *
     * @param string $text Text to split into words
     * @param string $expected Expected result
     *
     * @return void
     */
    public function testAnyCaseToWords(string $text, string $expected)
    {
        $actual = $this->codeFormatter->anyCaseToWords($text);
        $this->assertEquals($expected, $actual);
    }

    public function anyCaseToWordsTestSet(): array
    {
        return [
            ['simple', 'simple'],
            ['kebab-case', 'kebab case'],
            ['snake_case', 'snake case'],
            ['camelCase', 'camel case'],
            ['StudlyCase', 'studly case'],
            ['FBIAgency', 'f b i agency'],
        ];
    }
}
