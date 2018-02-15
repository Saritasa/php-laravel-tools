<?php

namespace Saritasa\LaravelTools\Services;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;

/**
 * Scaffold templates writer. Takes template, fills placeholders and writes result file.
 */
class TemplateWriter
{
    /**
     * Filesystem service.
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Scaffold templates writer. Takes template, fills placeholders and writes result file.
     *
     * @param Filesystem $filesystem Filesystem service
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Template file content.
     *
     * @var string
     */
    private $templateContent = null;

    /**
     * Retrieves template content. First step of template building process.
     *
     * @param string $templateName Template name to take
     *
     * @return TemplateWriter
     * @throws FileNotFoundException
     */
    public function take(string $templateName): self
    {
        if (!is_readable($templateName)) {
            throw new FileNotFoundException("Template file [{$templateName}] not found");
        }

        $this->templateContent = $this->filesystem->get($templateName);

        return $this;
    }

    /**
     * Checks that template content was successfully loaded.
     *
     * @return void
     */
    private function validateTemplateContent(): void
    {
        if (!$this->templateContent) {
            throw new \UnexpectedValueException('Template content is empty. Did You take() any template?');
        }
    }

    /**
     * Checks that template placeholders was successfully filled.
     *
     * @return void
     */
    private function validatePlaceholders(): void
    {
        $placeholders = false;
        if (preg_match_all('/\{\{([^{}]*)\}\}/', $this->templateContent, $placeholders)) {
            $notFilledPlaceholders = implode(', ', $placeholders[1]);
            throw new \UnexpectedValueException(
                "Template placeholder(s) [{$notFilledPlaceholders}] not filled. Did You fill() placeholders?"
            );
        }
    }

    /**
     * Fills placeholders in template. Second step of template building process.
     *
     * @param array $placeholders Array with key-value pairs where key is placeholder name
     * and value is placeholder's content
     *
     * @return TemplateWriter
     * @throws \Exception
     */
    public function fill(array $placeholders): self
    {
        $this->validateTemplateContent();

        foreach ($placeholders as $placeholder => $value) {
            $replacementsCount = 0;

            $this->templateContent = str_replace(
                "{{{$placeholder}}}",
                $value,
                $this->templateContent,
                $replacementsCount
            );

            if ($replacementsCount == 0) {
                throw new \Exception("Placeholder {{{$placeholder}}} not found in template");
            }
        }

        return $this;
    }

    /**
     * Write filled template to file. Last step of template building process.
     *
     * @param string $resultFileName Result file name to write.
     *
     * @return boolean
     */
    public function write(string $resultFileName): bool
    {
        $this->validateTemplateContent();

        $this->validatePlaceholders();

        return (bool)$this->filesystem->put($resultFileName, $this->templateContent);
    }
}
