<?php

namespace Saritasa\LaravelTools\Services;

use Illuminate\Config\Repository;
use RuntimeException;
use Saritasa\LaravelTools\DTO\DtoFactoryConfig;
use Saritasa\LaravelTools\Enums\PropertiesVisibilityTypes;
use Saritasa\LaravelTools\Enums\ScaffoldTemplates;
use Saritasa\LaravelTools\Factories\DtoFactory;

/**
 * DTO service. Allows to configure DTO factory.
 */
class DtoService
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
     * DTO factory.
     *
     * @var DtoFactory
     */
    private $dtoFactory;

    /**
     * DTO service. Allows to configure DTO factory.
     *
     * @param Repository $configRepository Application configuration repository
     * @param TemplatesManager $templatesManager Scaffold templates manager
     * @param DtoFactory $dtoFactory DTO factory
     */
    public function __construct(
        Repository $configRepository,
        TemplatesManager $templatesManager,
        DtoFactory $dtoFactory
    ) {
        $this->configRepository = $configRepository;
        $this->templatesManager = $templatesManager;
        $this->dtoFactory = $dtoFactory;
    }

    /**
     * Generates new DTO class
     *
     * @param string $modelClassName Model class name to which need to generate DTO
     * @param null|string $dtoClassName Result DTO class name. When not passed
     * then will be automatically generated according to model class name
     *
     * @return string Result DTO file name
     * @throws RuntimeException When DTO factory not correctly configured
     * @throws \Exception
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException When template file not found
     */
    public function generateDto(string $modelClassName, string $dtoClassName): string
    {
        $dtoFactoryConfiguration = $this->getFactoryConfiguration($modelClassName, $dtoClassName);

        return $this->dtoFactory->configure($dtoFactoryConfiguration)->build();
    }

    /**
     * Builds DTO factory configuration.
     *
     * @param string $modelClassName Target model class name
     * @param string $dtoClassName Result DTO file name
     *
     * @return DtoFactoryConfig
     * @throws RuntimeException
     * @throws \Saritasa\Exceptions\InvalidEnumValueException
     */
    private function getFactoryConfiguration(
        string $modelClassName,
        string $dtoClassName
    ): DtoFactoryConfig {

        return new DtoFactoryConfig([
            DtoFactoryConfig::NAMESPACE => $this->getDtosNamespace(),
            DtoFactoryConfig::PARENT_CLASS_NAME => $this->getDtoParentClassName(),
            DtoFactoryConfig::CLASS_NAME => $dtoClassName,
            DtoFactoryConfig::MODEL_CLASS_NAME => $this->getModelFullClassName($modelClassName),
            DtoFactoryConfig::RESULT_FILENAME => $this->getResultFileName($dtoClassName),
            DtoFactoryConfig::EXCLUDED_ATTRIBUTES => $this->getIgnoredAttributes(),
            DtoFactoryConfig::TEMPLATE_FILENAME => $this->getTemplateFileName(),
            DtoFactoryConfig::PROPERTIES_VISIBILITY => $this->getDtoPropertiesVisibilityType(),
        ]);
    }

    /**
     * Returns DTO target namespace.
     *
     * @return string
     * @throws RuntimeException When DTO namespace is empty
     */
    private function getDtosNamespace(): string
    {
        $namespace = $this->configRepository->get('laravel_tools.dto.namespace');

        if (!$namespace) {
            throw new RuntimeException('DTO namespace not configured');
        }

        return $namespace;
    }

    /**
     * Returns DTO parent class name.
     *
     * @return string
     * @throws RuntimeException When DTO parent is empty
     */
    private function getDtoParentClassName(): string
    {
        $parentClassName = $this->configRepository->get('laravel_tools.dto.parent');

        if (!$parentClassName) {
            throw new RuntimeException('DTO parent class name not configured');
        }

        return $parentClassName;
    }

    /**
     * Returns DTO properties visibility type.
     *
     * @return string
     * @throws \Saritasa\Exceptions\InvalidEnumValueException
     */
    private function getDtoPropertiesVisibilityType(): string
    {
        $visibilityType = $this->configRepository->get(
            'laravel_tools.dto.properties_visibility',
            PropertiesVisibilityTypes::PROTECTED
        );

        return (new PropertiesVisibilityTypes($visibilityType))->getValue();
    }

    /**
     * Returns fully-qualified model class name for which need to generate DTO.
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
     * Returns full path to new DTO.
     *
     * @param string $dtoName DTO name to retrieve path for
     *
     * @return string
     */
    private function getResultFileName(string $dtoName): string
    {
        $dtosPath = $this->configRepository->get('laravel_tools.dto.path');

        return rtrim($dtosPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $dtoName . '.php';
    }

    /**
     * Returns model attributes names that should be ignored by factory builder.
     *
     * @return array
     * @throws RuntimeException When ignored attributes configuration is not an array
     */
    private function getIgnoredAttributes(): array
    {
        $ignoredAttributes = $this->configRepository->get('laravel_tools.dto.except');

        if (!is_array($ignoredAttributes)) {
            throw new RuntimeException('DTO ignored attributes configuration is invalid');
        }

        return $ignoredAttributes;
    }

    /**
     * Returns DTO template file name.
     *
     * @return string
     */
    private function getTemplateFileName(): string
    {
        $templateFileName = $this->configRepository->get(
            'laravel_tools.dto.template_file_name',
            ScaffoldTemplates::DTO_TEMPLATE
        );

        return $this->templatesManager->getTemplatePath($templateFileName);
    }
}
