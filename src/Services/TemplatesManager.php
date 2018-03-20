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
        if (strpos($templateFileName, DIRECTORY_SEPARATOR) === false) {
            $pathTree = [__DIR__, '..', 'Templates', $templateFileName];
            $templateFileName = implode(DIRECTORY_SEPARATOR, $pathTree);
        }

        return $templateFileName;
    }
}
