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
        if (!$codeLine) {
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
        $codeLines = $this->blockToLines($codeBlock);
        foreach ($codeLines as $codeLine) {
            $results[] = $this->indentLine($codeLine, $ignoreExistingIndent, $indentSize);
        }

        return $this->linesToBlock($results);
    }

    /**
     * Concatenates list of lines into new-line separated block of text.
     *
     * @param string[] $lines List of lines to concatenate
     *
     * @return string
     */
    public function linesToBlock(array $lines): string
    {
        return implode("\n", $lines);
    }

    /**
     * Divides new-line separated block of text into list of lines
     *
     * @param string $block Block to separate
     *
     * @return array
     */
    public function blockToLines(string $block): array
    {
        return explode("\n", $block);
    }

    /**
     * Format passed sentence.
     *
     * @param string|null $sentence Sentence to format
     * @param bool $dotEnded Whether formatted sentence should be dot-ended or not
     *
     * @return string Sentence with upper cased first word and dot at the end. Just sentence in other words
     */
    public function toSentence(?string $sentence, bool $dotEnded = true): string
    {
        $sentence = rtrim(ucfirst(trim($sentence)), '.');

        if ($dotEnded) {
            $sentence .= '.';
        }

        return $sentence;
    }
}
