<?php

namespace Saritasa\LaravelTools\Factories;

use Saritasa\LaravelTools\CodeGenerators\CodeFormatter;
use Saritasa\LaravelTools\CodeGenerators\NamespaceExtractor;
use Saritasa\LaravelTools\DTO\Configs\ClassFactoryConfig;
use Saritasa\LaravelTools\Services\TemplateWriter;

/**
 * Factory to scaffold some new class based on template.
 */
abstract class ClassFactory extends TemplateBasedFactory
{
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
     * Namespace extractor. Allows to retrieve list of used namespaces from code and remove FQN from it.
     *
     * @var NamespaceExtractor
     */
    private $namespaceExtractor;

    /**
     * Factory to scaffold some new class based on template.
     *
     * @param TemplateWriter $templateWriter Templates files writer
     * @param CodeFormatter $codeFormatter Code style utility. Allows to format code according to settings
     * @param NamespaceExtractor $namespaceExtractor Namespace extractor. Allows to retrieve list of used namespaces
     *     from code and remove FQN from it
     */
    public function __construct(
        TemplateWriter $templateWriter,
        CodeFormatter $codeFormatter,
        NamespaceExtractor $namespaceExtractor
    ) {
        parent::__construct($templateWriter, $codeFormatter);
        $this->namespaceExtractor = $namespaceExtractor;
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
        $optimizedPlaceholder = $placeholder;
        $extractedClasses = $this->namespaceExtractor->extract($optimizedPlaceholder);
        $this->usedClasses = array_unique(array_merge($this->usedClasses, $extractedClasses));

        return $optimizedPlaceholder;
    }

    /**
     * Returns USE section of built class.
     *
     * @return string
     */
    protected function formatUsedClasses(): string
    {
        return $this->namespaceExtractor->format($this->usedClasses);
    }
}
