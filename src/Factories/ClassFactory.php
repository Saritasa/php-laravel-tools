<?php

namespace Saritasa\LaravelTools\Factories;

use Saritasa\LaravelTools\DTO\ClassFactoryConfig;
use Saritasa\LaravelTools\Services\TemplateWriter;

/**
 * Factory to scaffold some new class based on template.
 */
abstract class ClassFactory extends TemplateBasedFactory
{
    /**
     * Templates files writer.
     *
     * @var TemplateWriter
     */
    protected $templateWriter;

    /**
     * Factory configuration.
     *
     * @var ClassFactoryConfig
     */
    protected $config;

    /**
     * Array of used in generated class imports.
     *
     * @var string[]
     */
    protected $usedClasses = [];

    /**
     * Extract and replace fully-qualified class names from placeholder.
     *
     * @param string $placeholder Placeholder to extract class names from
     *
     * @return string Optimized placeholder
     */
    protected function extractUsedClasses($placeholder): string
    {
        $classNamespaceRegExp = '/([\\\\a-zA-Z0-9_]*\\\\[\\\\a-zA-Z0-9_]*)/';
        $matches = [];
        $optimizedPlaceholder = $placeholder;
        if (preg_match_all($classNamespaceRegExp, $placeholder, $matches)) {
            foreach ($matches[1] as $match) {
                $usedClassName = $match;
                $this->usedClasses[] = trim($usedClassName, '\\');
                $namespaceParts = explode('\\', $usedClassName);
                $resultClassName = array_pop($namespaceParts);
                $optimizedPlaceholder = str_replace($usedClassName, $resultClassName, $optimizedPlaceholder);
            }
        }

        $this->usedClasses = array_unique($this->usedClasses);

        return $optimizedPlaceholder;
    }

    /**
     * Returns USE section of built class.
     *
     * @return string
     */
    protected function formatUsedClasses(): string
    {
        $result = [];
        foreach ($this->usedClasses as $usedClass) {
            $result[] = "use {$usedClass};";
        }

        sort($result);

        return implode("\n", $result);
    }
}
