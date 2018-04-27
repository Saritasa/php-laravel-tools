<?php

namespace Saritasa\LaravelTools\Services\GenerationServices;

use Exception;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Saritasa\Exceptions\ConfigurationException;
use Saritasa\LaravelTools\DTO\Configs\FormRequestFactoryConfig;
use Saritasa\LaravelTools\Factories\FormRequestFactory;
use Saritasa\LaravelTools\Services\TemplatesManager;

/**
 * Form request service. Allows to configure form request factory.
 */
class FormRequestGenerationService extends ClassGenerationService
{
    /**
     * Section key in configuration repository where configuration for this service located.
     *
     * @var string
     */
    protected $serviceConfigurationKey = 'form_requests';

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
     *
     * @throws ConfigurationException
     */
    public function __construct(
        Repository $configRepository,
        TemplatesManager $templatesManager,
        FormRequestFactory $formRequestFactory
    ) {
        parent::__construct($configRepository, $templatesManager);
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
            FormRequestFactoryConfig::NAMESPACE => $this->getClassNamespace(),
            FormRequestFactoryConfig::PARENT_CLASS_NAME => $this->getParentClassName(),
            FormRequestFactoryConfig::TEMPLATE_FILENAME => $this->getTemplateFileName(),
            FormRequestFactoryConfig::RESULT_FILENAME => $this->getResultFileName($formRequestClassName),
            FormRequestFactoryConfig::CLASS_NAME => $formRequestClassName,
            FormRequestFactoryConfig::MODEL_CLASS_NAME => $this->getModelFullClassName($modelClassName),
            FormRequestFactoryConfig::EXCLUDED_ATTRIBUTES => $this->getIgnoredAttributes(),
            FormRequestFactoryConfig::SUGGEST_ATTRIBUTE_NAMES_CONSTANTS => $this->getSuggestAttributesConstants(),
        ]);
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
        $modelsNamespace = trim($this->getPackageConfig('models.namespace'), '\\');

        return "{$modelsNamespace}\\{$model}";
    }

    /**
     * Returns model attributes names that should be ignored by factory builder.
     *
     * @return array
     * @throws ConfigurationException When ignored attributes configuration is not an array
     */
    private function getIgnoredAttributes(): array
    {
        $ignoredAttributes = $this->getServiceConfig('except');

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
        return $this->getServiceConfig('suggest_attribute_names_constants', true);
    }
}
