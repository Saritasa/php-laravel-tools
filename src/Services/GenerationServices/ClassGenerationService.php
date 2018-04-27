<?php

namespace Saritasa\LaravelTools\Services\GenerationServices;

use Saritasa\Exceptions\ConfigurationException;

/**
 * Parent class for class-generation services. Contains initial and class-generation specific configuration methods.
 */
abstract class ClassGenerationService extends TemplateBasedGenerationService
{
    /**
     * Returns class target namespace.
     *
     * @return string
     * @throws ConfigurationException When class namespace is empty
     */
    protected function getClassNamespace(): string
    {
        $namespace = $this->getServiceConfig('namespace');

        if (!$namespace) {
            throw new ConfigurationException('Class namespace not configured');
        }

        return $namespace;
    }

    /**
     * Returns parent class name.
     *
     * @return string
     * @throws ConfigurationException When parent is empty
     */
    protected function getParentClassName(): string
    {
        $parentClassName = $this->getServiceConfig('parent');

        if (!$parentClassName) {
            throw new ConfigurationException('Parent class name not configured');
        }

        return $parentClassName;
    }

    /**
     * Returns full path where generated class should be located.
     *
     * @return string Path without director separator in the end
     */
    protected function getClassesLocation(): string
    {
        return rtrim($this->getServiceConfig('path'), DIRECTORY_SEPARATOR);
    }

    /**
     * Returns full path to new class file that should be generated.
     *
     * @param string $className Class name to retrieve full path for
     *
     * @return string
     */
    protected function getResultFileName(string $className): string
    {
        return $this->getClassesLocation() . DIRECTORY_SEPARATOR . $className . '.php';
    }
}
