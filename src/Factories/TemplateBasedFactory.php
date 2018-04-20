<?php

namespace Saritasa\LaravelTools\Factories;

use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Saritasa\Exceptions\ConfigurationException;
use Saritasa\LaravelTools\CodeGenerators\CodeStyler;
use Saritasa\LaravelTools\DTO\ClassFactoryConfig;
use Saritasa\LaravelTools\DTO\TemplateBasedFactoryConfig;
use Saritasa\LaravelTools\Services\TemplateWriter;

/**
 * Factory to scaffold some new file based on template.
 */
abstract class TemplateBasedFactory
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
     * Code style utility. Allows to format code according to settings.
     *
     * @var CodeStyler
     */
    protected $codeStyler;

    /**
     * Factory to scaffold some new class based on template.
     *
     * @param TemplateWriter $templateWriter Templates files writer
     * @param CodeStyler $codeStyler Code style utility. Allows to format code according to settings
     */
    public function __construct(TemplateWriter $templateWriter, CodeStyler $codeStyler)
    {
        $this->templateWriter = $templateWriter;
        $this->codeStyler = $codeStyler;
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
     * Validate factory configuration.
     *
     * @return void
     * @throws ConfigurationException
     */
    protected function validateConfig(): void
    {
        if (!$this->config) {
            throw new ConfigurationException('Configuration empty. Please, configure() factory first');
        }
    }

    /**
     * Configure factory to build new class.
     *
     * @param TemplateBasedFactoryConfig $config Class configuration
     *
     * @return static
     * @throws ConfigurationException
     */
    public function configure(TemplateBasedFactoryConfig $config)
    {
        $this->config = $config;

        $this->validateConfig();

        return $this;
    }

    /**
     * Returns template's placeholders values.
     *
     * @return array
     * @throws Exception
     */
    abstract protected function getPlaceHoldersValues(): array;
}
