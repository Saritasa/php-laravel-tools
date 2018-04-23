<?php

namespace Saritasa\LaravelTools\CodeGenerators;

use Illuminate\Config\Repository;

/**
 * Code style utility. Allows to format code according to settings.
 * Can apply valid indent to code line or code block.
 */
class CodeFormatter
{
    /**
     * Default code indent.
     *
     * @var string
     */
    private $indent = '    ';

    /**
     * Code style utility. Allows to format code according to settings. Can apply valid indent to code line or code
     * block.
     *
     * @param Repository $configRepository Configuration storage
     */
    public function __construct(Repository $configRepository)
    {
        $this->indent = $configRepository->get('laravel_tools.code_style.indent', $this->indent);
    }

    /**
     * Applies indent to code line.
     *
     * @param string $codeLine Code line that should be prepended with indent
     * @param bool $ignoreExistingIndent Should indent in line be ignored and then new be applied
     * @param int $indentSize How many indents should be applied
     *
     * @return string
     */
    public function indentLine(string $codeLine, bool $ignoreExistingIndent = false, int $indentSize = 1): string
    {
        if (empty(trim($codeLine))) {
            return $codeLine;
        }

        if ($ignoreExistingIndent) {
            $codeLine = ltrim($codeLine);
        }

        $additionalIndent = str_repeat($this->indent, $indentSize);

        return "{$additionalIndent}{$codeLine}";
    }

    /**
     * Applies indent to code block.
     *
     * @param string $codeBlock Code block that should be prepended with indent
     * @param bool $ignoreExistingIndent Should indent on each line of block be ignored and then new be applied
     * @param int $indentSize How many indents should be applied
     *
     * @return string
     */
    public function indentBlock(string $codeBlock, bool $ignoreExistingIndent = false, int $indentSize = 1): string
    {
        $results = [];
        $codeLines = explode("\n", $codeBlock);
        foreach ($codeLines as $codeLine) {
            $results[] = $this->indentLine($codeLine, $ignoreExistingIndent, $indentSize);
        }

        return implode("\n", $results);
    }
}
