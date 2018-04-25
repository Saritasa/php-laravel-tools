<?php

namespace Saritasa\LaravelTools\CodeGenerators;

/**
 * Namespace extractor. Allows to retrieve list of used namespaces from code and remove FQN from it.
 */
class NamespaceExtractor
{
    /**
     *
     * Code style utility. Allows to format code according to settings. Can apply valid indent to code line or code
     * block.
     *
     * @var CodeFormatter
     */
    private $codeFormatter;

    /**
     * Namespace extractor. Allows to retrieve list of used namespaces from code and remove FQN from it.
     *
     * @param CodeFormatter $codeFormatter Code style utility. Allows to format code according to settings. Can apply
     *     valid indent to code line or code block
     */
    public function __construct(CodeFormatter $codeFormatter)
    {
        $this->codeFormatter = $codeFormatter;
    }

    /**
     * Extract and replace fully-qualified class names by simple class name from block of code.
     *
     * @param string $code Code block from which need to extract namespaces. Will be replaced by optimized code
     *
     * @return string[] List of extracted placeholders
     */
    public function extract(string &$code): array
    {
        $classNamespaceRegExp = '/([\\\\a-zA-Z0-9_]*\\\\[\\\\a-zA-Z0-9_]*)/';
        $optimizedCode = $code;
        $usedClasses = [];
        $matches = [];
        if (preg_match_all($classNamespaceRegExp, $code, $matches)) {
            foreach ($matches[1] as $match) {
                $usedClassName = $match;
                $usedClasses[] = trim($usedClassName, '\\');
                $namespaceParts = explode('\\', $usedClassName);
                $resultClassName = array_pop($namespaceParts);
                $optimizedCode = str_replace($usedClassName, $resultClassName, $optimizedCode);
            }
        }

        $code = $optimizedCode;

        return array_unique($usedClasses);
    }

    /**
     * Formats list of passed namespaces as USES section of PHP class.
     *
     * @param string[] $usedClasses List of classes to import
     *
     * @return string
     */
    public function format(array $usedClasses): string
    {
        $result = [];
        foreach ($usedClasses as $usedClass) {
            $result[] = "use {$usedClass};";
        }

        sort($result);

        return $this->codeFormatter->linesToBlock($result);
    }
}
