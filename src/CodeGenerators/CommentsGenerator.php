<?php

namespace Saritasa\LaravelTools\CodeGenerators;

/**
 * Php comments generator. Allows to comment lines and blocks of text.
 */
class CommentsGenerator
{
    private const ADDITIONAL_SLASHES_TO_ALTERNATIVE_BLOCK_COMMENT = 6;

    /**
     * Comments passed line as a single line delimiter
     *
     * @param string $lineToComment Line to comment
     *
     * @return string
     */
    public function line(string $lineToComment): string
    {
        $lineToComment = trim($lineToComment);

        return "// $lineToComment";
    }

    /**
     * Comments passed block of text as multi line comment.
     *
     * @param string $blockToComment Newline separated block to comment
     *
     * @return string
     */
    public function block(string $blockToComment): string
    {
        $lines = explode("\n", trim($blockToComment));
        $result = [];
        $result[] = '/**';
        foreach ($lines as $line) {
            $line = trim($line);
            $result[] = rtrim(" * {$line}");
        }
        $result[] = ' */';

        return implode("\n", $result);
    }

    /**
     * Commented by slashes block. Just alternative version of block commenting to increase readability in code.
     *
     * @param string $blockToComment Newline separated block to comment
     *
     * @return string
     */
    public function alternativeBlock(string $blockToComment): string
    {
        $lines = explode("\n", trim($blockToComment));

        $maxLength = 0;
        foreach ($lines as $line) {
            $maxLength = max($maxLength, strlen(trim($line)));
        }

        $blockCommentDelimiter = str_repeat('/', $maxLength + static::ADDITIONAL_SLASHES_TO_ALTERNATIVE_BLOCK_COMMENT);

        $result = [];
        $result[] = $blockCommentDelimiter;
        foreach ($lines as $line) {
            $line = str_pad(trim($line), $maxLength, ' ', STR_PAD_RIGHT);
            $result[] = "// {$line} //";
        }
        $result[] = $blockCommentDelimiter;

        return implode("\n", $result);
    }
}
