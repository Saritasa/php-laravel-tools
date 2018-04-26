<?php

namespace Saritasa\LaravelTools\Services;

use Exception;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Saritasa\Exceptions\ConfigurationException;
use Saritasa\LaravelTools\DTO\Configs\DtoFactoryConfig;
use Saritasa\LaravelTools\Factories\DtoFactory;

/**
 * DTO service. Allows to configure DTO factory.
 */
class DtoGenerationService extends ClassGenerationService
{
    /**
     * Section key in configuration repository where configuration for this service located.
     *
     * @var string
     */
    protected $serviceConfigurationKey = 'dto';

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
     *
     * @throws ConfigurationException
     */
    public function __construct(
        Repository $configRepository,
        TemplatesManager $templatesManager,
        DtoFactory $dtoFactory
    ) {
        parent::__construct($configRepository, $templatesManager);
        $this->dtoFactory = $dtoFactory;
    }

    /**
     * Generates new DTO class.
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
        $factoryConfig = $this->getConfiguration($modelClassName, $dtoClassName, $initialFactoryConfig);

        return $this->dtoFactory->configure($factoryConfig)->build();
    }

    /**
     * Returns default configuration for DTO factory.
     *
     * @param string $modelClassName Target model class name
     * @param string $dtoClassName Result DTO file name
     * @param DtoFactoryConfig $initialFactoryConfig Initial configuration
     *
     * @return DtoFactoryConfig
     * @throws ConfigurationException
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
                $dtoParentClassConfig = 'immutable_strict_type_parent';
                break;
            case $immutable:
                $dtoParentClassConfig = 'immutable_parent';
                break;
            case $strict:
                $dtoParentClassConfig = 'strict_type_parent';
                break;
            default:
                $dtoParentClassConfig = 'parent';
        }

        $dtoParent = $this->getServiceConfig($dtoParentClassConfig);

        $withConstants = is_null($initialFactoryConfig->withConstants)
            ? $this->isConstantsNeed()
            : $initialFactoryConfig->withConstants;

        return new DtoFactoryConfig([
            DtoFactoryConfig::NAMESPACE => $this->getClassNamespace(),
            DtoFactoryConfig::TEMPLATE_FILENAME => $this->getTemplateFileName(),
            DtoFactoryConfig::PARENT_CLASS_NAME => $dtoParent,
            DtoFactoryConfig::RESULT_FILENAME => $this->getResultFileName($dtoClassName),
            DtoFactoryConfig::MODEL_CLASS_NAME => $this->getModelFullClassName($modelClassName),
            DtoFactoryConfig::CLASS_NAME => $dtoClassName,
            DtoFactoryConfig::EXCLUDED_ATTRIBUTES => $this->getIgnoredAttributes(),
            DtoFactoryConfig::IMMUTABLE => $immutable,
            DtoFactoryConfig::STRICT_TYPES => $strict,
            DtoFactoryConfig::WITH_CONSTANTS => $withConstants,
        ]);
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
            throw new ConfigurationException('DTO ignored attributes configuration is invalid');
        }

        return $ignoredAttributes;
    }

    /**
     * Whether constants generation is necessary.
     *
     * @return boolean
     */
    private function isConstantsNeed(): bool
    {
        return (bool)$this->getServiceConfig('with_constants');
    }

    /**
     * Whether DTO should be immutable.
     *
     * @return boolean
     */
    private function immutable(): bool
    {
        return (bool)$this->getServiceConfig('immutable');
    }

    /**
     * Whether DTO should be strict typed.
     *
     * @return boolean
     */
    private function strictTypes(): bool
    {
        return (bool)$this->getServiceConfig('strict');
    }
}
