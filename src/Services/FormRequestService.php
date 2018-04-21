<?php

namespace Saritasa\LaravelTools\Services;

use Exception;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Saritasa\Exceptions\ConfigurationException;
use Saritasa\LaravelTools\DTO\FormRequestFactoryConfig;
use Saritasa\LaravelTools\Enums\ScaffoldTemplates;
use Saritasa\LaravelTools\Factories\FormRequestFactory;

/**
 * Form request service. Allows to configure form request factory.
 */
class FormRequestService
{
    /**
     * Application configuration repository.
     *
     * @var Repository
     */
    private $configRepository;

    /**
     * Scaffold templates manager.
     *
     * @var TemplatesManager
     */
    private $templatesManager;

    /**
     * Form request factory.
     *
     * @var FormRequestFactory
     */
    private $formRequestFactory;

    /**
     * Form request service. Allows to configure form request factory.
     *
     * @param Repository $configRepository Application configuration repository
     * @param TemplatesManager $templatesManager Scaffold templates manager
     * @param FormRequestFactory $formRequestFactory Form request factory
     */
    public function __construct(
        Repository $configRepository,
        TemplatesManager $templatesManager,
        FormRequestFactory $formRequestFactory
    ) {
        $this->configRepository = $configRepository;
        $this->templatesManager = $templatesManager;
        $this->formRequestFactory = $formRequestFactory;
    }

    /**
     * Generates new Form Request class.
     *
     * @param string $modelClassName Model class name to which need to generate request
     * @param null|string $formRequestClassName Result form request class name. When not passed
     * then will be automatically generated according to model class name
     *
     * @return string Result form request file name
     * @throws ConfigurationException When form request factory not correctly configured
     * @throws Exception
     * @throws FileNotFoundException When template file not found
     */
    public function generateFormRequest(string $modelClassName, string $formRequestClassName): string
    {
        $factoryConfig = $this->getDefaultConfiguration($modelClassName, $formRequestClassName);

        return $this->formRequestFactory->configure($factoryConfig)->build();
    }

    /**
     * Returns default configuration for request factory.
     *
     * @param string $modelClassName Target model class name
     * @param string $formRequestClassName Result form request file name
     *
     * @return FormRequestFactoryConfig
     * @throws ConfigurationException
     */
    private function getDefaultConfiguration(
        string $modelClassName,
        string $formRequestClassName
    ): FormRequestFactoryConfig {
        return new FormRequestFactoryConfig([
            FormRequestFactoryConfig::NAMESPACE => $this->getFormRequestsNamespace(),
            FormRequestFactoryConfig::PARENT_CLASS_NAME => $this->getFormRequestParentClassName(),
            FormRequestFactoryConfig::CLASS_NAME => $formRequestClassName,
            FormRequestFactoryConfig::MODEL_CLASS_NAME => $this->getModelFullClassName($modelClassName),
            FormRequestFactoryConfig::RESULT_FILENAME => $this->getResultFileName($formRequestClassName),
            FormRequestFactoryConfig::TEMPLATE_FILENAME => $this->getTemplateFileName(),
            FormRequestFactoryConfig::EXCLUDED_ATTRIBUTES => $this->getIgnoredAttributes(),
            FormRequestFactoryConfig::SUGGEST_ATTRIBUTE_NAMES_CONSTANTS => $this->getSuggestAttributesConstants(),
        ]);
    }

    /**
     * Returns form request target namespace.
     *
     * @return string
     * @throws ConfigurationException When form request namespace is empty
     */
    private function getFormRequestsNamespace(): string
    {
        $namespace = $this->configRepository->get('laravel_tools.form_requests.namespace');

        if (!$namespace) {
            throw new ConfigurationException('Form request namespace not configured');
        }

        return $namespace;
    }

    /**
     * Returns form request parent class name.
     *
     * @return string
     * @throws ConfigurationException When form request parent is empty
     */
    private function getFormRequestParentClassName(): string
    {
        $parentClassName = $this->configRepository->get('laravel_tools.form_requests.parent');

        if (!$parentClassName) {
            throw new ConfigurationException('Form request parent class name not configured');
        }

        return $parentClassName;
    }

    /**
     * Returns fully-qualified model class name for which need to generate form request.
     *
     * @param string $model Model class name
     *
     * @return string
     */
    private function getModelFullClassName(string $model): string
    {
        $modelsNamespace = trim($this->configRepository->get('laravel_tools.models.namespace'), '\\');

        return "{$modelsNamespace}\\{$model}";
    }

    /**
     * Returns full path to new form request.
     *
     * @param string $formRequestName Form Request name to retrieve path for
     *
     * @return string
     */
    private function getResultFileName(string $formRequestName): string
    {
        $formRequestsPath = $this->configRepository->get('laravel_tools.form_requests.path');

        return rtrim($formRequestsPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $formRequestName . '.php';
    }

    /**
     * Returns model attributes names that should be ignored by factory builder.
     *
     * @return array
     * @throws ConfigurationException When ignored attributes configuration is not an array
     */
    private function getIgnoredAttributes(): array
    {
        $ignoredAttributes = $this->configRepository->get('laravel_tools.form_requests.except');

        if (!is_array($ignoredAttributes)) {
            throw new ConfigurationException('Form request ignored attributes configuration is invalid');
        }

        return $ignoredAttributes;
    }

    /**
     * Returns true if need to use constants instead of string attributes names.
     *
     * @return boolean
     */
    private function getSuggestAttributesConstants(): bool
    {
        return $this->configRepository->get('laravel_tools.models.suggest_attribute_names_constants', true);
    }

    /**
     * Returns form request template file name.
     *
     * @return string
     */
    private function getTemplateFileName(): string
    {
        $templateFileName = $this->configRepository->get(
            'laravel_tools.form_requests.template_file_name',
            ScaffoldTemplates::FORM_REQUEST_TEMPLATE
        );

        return $this->templatesManager->getTemplatePath($templateFileName);
    }
}
