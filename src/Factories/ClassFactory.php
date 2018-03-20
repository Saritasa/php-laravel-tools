<?php

namespace Saritasa\LaravelTools\Factories;

use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Saritasa\Dto;
use Saritasa\LaravelTools\DTO\ClassFactoryConfig;
use Saritasa\LaravelTools\Services\TemplateWriter;

/**
 * Factory to scaffold some new class based on template.
 */
abstract class ClassFactory
{
    protected const INDENT_SIZE = 4;

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
     * Factory to scaffold some new class based on template.
     *
     * @param TemplateWriter $templateWriter Templates files writer
     */
    public function __construct(TemplateWriter $templateWriter)
    {
        $this->templateWriter = $templateWriter;
    }

    /**
     * Build and write new class file.
     *
     * @return string Result file name
     * @throws Exception
     * @throws FileNotFoundException
     */
    public function build(): string
    {
        $filledPlaceholders = $this->getPlaceHoldersValues();

        $this->templateWriter
            ->take($this->config->templateFilename)
            ->fill($filledPlaceholders)
            ->write($this->config->resultFilename);

        return $this->config->resultFilename;
    }

    /**
     * Configure factory to build new class.
     *
     * @param ClassFactoryConfig|Dto $config Class configuration
     *
     * @return static
     */
    abstract public function configure($config);

    /**
     * Returns template's placeholders values.
     *
     * @return array
     * @throws Exception
     */
    abstract protected function getPlaceHoldersValues(): array;

    /**
     * Returns indent for given size.
     *
     * @param int $size Necessary indent size
     *
     * @return string
     */
    protected function getIndent(int $size = 1): string
    {
        return str_repeat(' ', static::INDENT_SIZE * $size);
    }

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
