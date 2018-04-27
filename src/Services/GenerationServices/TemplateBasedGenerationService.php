<?php

namespace Saritasa\LaravelTools\Services\GenerationServices;

use Illuminate\Config\Repository;
use Saritasa\Exceptions\ConfigurationException;
use Saritasa\LaravelTools\Services\TemplatesManager;

/**
 * Parent class for template-based generation services. Contains initial configuration methods.
 */
abstract class TemplateBasedGenerationService
{
    /**
     * Section key in configuration repository where configuration for this service located.
     *
     * @var string
     */
    protected $serviceConfigurationKey;

    /**
     * Application configuration repository.
     *
     * @var Repository
     */
    protected $configRepository;

    /**
     * Scaffold templates manager.
     *
     * @var TemplatesManager
     */
    protected $templatesManager;

    /**
     * Parent class for template-based generation services. Contains initial configuration methods.
     *
     * @param Repository $configRepository Application configuration repository
     * @param TemplatesManager $templatesManager Scaffold templates manager
     *
     * @throws ConfigurationException When service configuration key is empty
     */
    public function __construct(Repository $configRepository, TemplatesManager $templatesManager)
    {
        $this->configRepository = $configRepository;
        $this->templatesManager = $templatesManager;

        if (!$this->serviceConfigurationKey) {
            throw new ConfigurationException('Service configuration key not configured');
        }
    }

    /**
     * Returns class template full path file name.
     *
     * @return string
     * @throws ConfigurationException
     */
    protected function getTemplateFileName(): string
    {
        $templateFileName = $this->getServiceConfig('template_file_name');

        if (!$templateFileName) {
            throw new ConfigurationException('Template file not configured');
        }

        return $this->templatesManager->getTemplatePath($templateFileName);
    }

    /**
     * Returns configuration of own package.
     *
     * @param string $key Configuration key
     * @param mixed|null $default Default value if config is empty
     *
     * @return mixed
     */
    protected function getPackageConfig(string $key, $default = null)
    {
        return $this->configRepository->get("laravel_tools.{$key}", $default);
    }

    /**
     * Returns configuration of service.
     *
     * @param string $key Configuration key
     * @param mixed|null $default Default value if config is empty
     *
     * @return mixed
     */
    protected function getServiceConfig(string $key, $default = null)
    {
        return $this->getPackageConfig("{$this->serviceConfigurationKey}.{$key}", $default);
    }
}
