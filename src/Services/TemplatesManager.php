<?php

namespace Saritasa\LaravelTools\Services;

/**
 * Scaffold templates manager. Allows to retrieve full path name to template.
 */
class TemplatesManager
{
    /**
     * Returns full path name to template file
     *
     * @param string $templateFileName Template file name to retrieve full path name to
     *
     * @return string
     */
    public function getTemplatePath(string $templateFileName): string
    {
        $pathTree = [__DIR__, '..', 'templates', $templateFileName];

        return implode(DIRECTORY_SEPARATOR, $pathTree);
    }
}
