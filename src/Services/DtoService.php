<?php

namespace Saritasa\LaravelTools\Services;

use Exception;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use RuntimeException;
use Saritasa\LaravelTools\DTO\DtoFactoryConfig;
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
     * @param DtoFactoryConfig $initialFactoryConfig Initial configuration
     *
     * @return string Result DTO file name
     * @throws Exception
     * @throws FileNotFoundException When template file not found
     */
    public function generateDto(
        string $modelClassName,
        string $dtoClassName,
        DtoFactoryConfig $initialFactoryConfig
    ): string {
        $dtoFactoryConfiguration = $this->getConfiguration($modelClassName, $dtoClassName, $initialFactoryConfig);

        return $this->dtoFactory->configure($dtoFactoryConfiguration)->build();
    }

    /**
     * Returns default configuration for DTO factory.
     *
     * @param string $modelClassName Target model class name
     * @param string $dtoClassName Result DTO file name
     * @param DtoFactoryConfig $initialFactoryConfig Initial configuration
     *
     * @return DtoFactoryConfig
     */
    private function getConfiguration(
        string $modelClassName,
        string $dtoClassName,
        DtoFactoryConfig $initialFactoryConfig
    ): DtoFactoryConfig {
        $strict = is_null($initialFactoryConfig->strictTypes)
            ? $this->strictTypes()
            : $initialFactoryConfig->strictTypes;

        $immutable = is_null($initialFactoryConfig->immutable)
            ? $this->immutable()
            : $initialFactoryConfig->immutable;

        // Choose parent for DTO
        switch (true) {
            case $strict && $immutable:
                $dtoParentClassConfig = 'laravel_tools.dto.immutable_strict_type_parent';
                break;
            case $immutable:
                $dtoParentClassConfig = 'laravel_tools.dto.immutable_parent';
                break;
            case $strict;
                $dtoParentClassConfig = 'laravel_tools.dto.strict_type_parent';
                break;
            default:
                $dtoParentClassConfig = 'laravel_tools.dto.parent';
        }

        $dtoParent = $this->configRepository->get($dtoParentClassConfig);

        $withConstants = is_null($initialFactoryConfig->withConstants)
            ? $this->isConstantsNeed()
            : $initialFactoryConfig->withConstants;

        return new DtoFactoryConfig([
            DtoFactoryConfig::NAMESPACE => $this->getDtosNamespace(),
            DtoFactoryConfig::CLASS_NAME => $dtoClassName,
            DtoFactoryConfig::MODEL_CLASS_NAME => $this->getModelFullClassName($modelClassName),
            DtoFactoryConfig::RESULT_FILENAME => $this->getResultFileName($dtoClassName),
            DtoFactoryConfig::EXCLUDED_ATTRIBUTES => $this->getIgnoredAttributes(),
            DtoFactoryConfig::TEMPLATE_FILENAME => $this->getTemplateFileName(),
            DtoFactoryConfig::PARENT_CLASS_NAME => $dtoParent,
            DtoFactoryConfig::IMMUTABLE => $immutable,
            DtoFactoryConfig::STRICT_TYPES => $strict,
            DtoFactoryConfig::WITH_CONSTANTS => $withConstants,
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

    /**
     * Whether constants generation is necessary.
     *
     * @return boolean
     */
    private function isConstantsNeed(): bool
    {
        return (bool)$this->configRepository->get('laravel_tools.dto.with_constants');
    }

    /**
     * Whether DTO should be immutable.
     *
     * @return boolean
     */
    private function immutable(): bool
    {
        return (bool)$this->configRepository->get('laravel_tools.dto.immutable');
    }

    /**
     * Whether DTO should be strict typed.
     *
     * @return boolean
     */
    private function strictTypes(): bool
    {
        return (bool)$this->configRepository->get('laravel_tools.dto.strict');
    }
}
