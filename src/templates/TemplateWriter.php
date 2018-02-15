<?php

namespace Saritasa\LaravelTools\templates;

use Illuminate\Contracts\Filesystem\FileNotFoundException;

/**
 * Scaffold templates writer. Takes template, fills placeholders and writes result file.
 */
class TemplateWriter
{
    /**
     * Template file content.
     *
     * @var string
     */
    private $templateContent = null;

    /**
     * Determines is placeholders in taken template was filled.
     *
     * @var bool
     */
    private $placeholdersFilled = false;

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
        $templateFullName = __DIR__ . DIRECTORY_SEPARATOR . $templateName;

        if (!is_readable($templateFullName)) {
            throw new FileNotFoundException("Template file [{$templateFullName}] not found");
        }

        $this->templateContent = file_get_contents($templateFullName);
        $this->placeholdersFilled = false;

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
        if (!$this->placeholdersFilled) {
            throw new \UnexpectedValueException('Template placeholders not filled. Did You fill() placeholders?');
        }
    }

    /**
     * Fills placeholders in template. Second step of template building process.
     *
     * @param array $placeholders
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
                throw new \Exception("Placeholder {{[{$placeholder}}} not found in template");
            }
            $this->placeholdersFilled = true;
        }

        return $this;
    }

    /**
     * Write filled template to file. Last step of template building process.
     *
     * @param string $resultFileName Result file name to write.
     *
     * @return bool
     */
    public function write(string $resultFileName): bool
    {
        $this->validateTemplateContent();

        $this->validatePlaceholders();

        return (bool)file_put_contents($resultFileName, $this->templateContent);
    }
}