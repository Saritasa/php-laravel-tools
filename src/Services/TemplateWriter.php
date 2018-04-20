<?php

namespace Saritasa\LaravelTools\Services;

use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use UnexpectedValueException;

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
     * Template file content.
     *
     * @var string
     */
    private $templateContent = null;

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

        $content = $this->filesystem->get($templateName);

        $this->setTemplateContent($content);

        return $this;
    }

    /**
     * Fills placeholders in template. Second step of template building process.
     *
     * @param array $placeholders Array with key-value pairs where key is placeholder name
     * and value is placeholder's content
     *
     * @return TemplateWriter
     * @throws Exception
     */
    public function fill(array $placeholders): self
    {
        $content = $this->getTemplateContent();

        foreach ($placeholders as $placeholder => $value) {
            $replacementsCount = 0;

            $content = str_replace(
                "{{{$placeholder}}}",
                $value,
                $content,
                $replacementsCount
            );

            $this->setTemplateContent($content);

            if ($replacementsCount == 0) {
                throw new UnexpectedValueException("Placeholder {{{$placeholder}}} not found in template");
            }
        }

        return $this;
    }

    /**
     * Returns current template content value.
     *
     * @return string
     */
    public function getTemplateContent(): string
    {
        if (!$this->templateContent) {
            throw new UnexpectedValueException('Template content is empty. Did You take() any template?');
        }

        return $this->templateContent;
    }

    /**
     * Set current template content.
     *
     * @param string $templateContent Template content to set as current
     */
    public function setTemplateContent(string $templateContent): void
    {
        $this->templateContent = $templateContent;
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
        $this->validatePlaceholders();

        return (bool)$this->filesystem->put($resultFileName, $this->getTemplateContent());
    }

    /**
     * Checks that template placeholders was successfully filled.
     *
     * @return void
     */
    private function validatePlaceholders(): void
    {
        $placeholders = false;
        if (preg_match_all('/\{\{([^{}]*)\}\}/', $this->getTemplateContent(), $placeholders)) {
            $notFilledPlaceholders = implode(', ', $placeholders[1]);
            throw new UnexpectedValueException(
                "Template placeholder(s) [{$notFilledPlaceholders}] not filled. Did You fill() placeholders?"
            );
        }
    }
}
