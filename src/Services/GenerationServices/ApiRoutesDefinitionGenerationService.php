<?php

namespace Saritasa\LaravelTools\Services\GenerationServices;

use Exception;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Saritasa\Exceptions\ConfigurationException;
use Saritasa\LaravelTools\DTO\Configs\ApiRoutesFactoryConfig;
use Saritasa\LaravelTools\Factories\ApiRoutesDeclarationFactory;
use Saritasa\LaravelTools\Services\TemplatesManager;

/**
 * Generates api routes definition based on swagger specification.
 */
class ApiRoutesDefinitionGenerationService extends TemplateBasedGenerationService
{
    /**
     * Section key in configuration repository where configuration for this service located.
     *
     * @var string
     */
    protected $serviceConfigurationKey = 'api_routes';

    /**
     * API routes file generator.
     *
     * @var ApiRoutesDeclarationFactory
     */
    private $apiRoutesDeclarationFactory;

    /**
     * DTO service. Allows to configure DTO factory.
     *
     * @param Repository $configRepository Application configuration repository
     * @param TemplatesManager $templatesManager Scaffold templates manager
     * @param ApiRoutesDeclarationFactory $apiRoutesDeclarationFactory API routes file generator
     *
     * @throws ConfigurationException
     */
    public function __construct(
        Repository $configRepository,
        TemplatesManager $templatesManager,
        ApiRoutesDeclarationFactory $apiRoutesDeclarationFactory
    ) {
        parent::__construct($configRepository, $templatesManager);
        $this->apiRoutesDeclarationFactory = $apiRoutesDeclarationFactory;
    }

    /**
     * Generates api routes definition based on swagger specification.
     *
     * @param ApiRoutesFactoryConfig $initialFactoryConfig Initial configuration
     *
     * @return string Result api routes file name
     * @throws Exception
     * @throws FileNotFoundException When template file not found
     */
    public function generateApiRoutes(ApiRoutesFactoryConfig $initialFactoryConfig): string
    {
        $factoryConfig = $this->getConfiguration($initialFactoryConfig);

        return $this->apiRoutesDeclarationFactory->configure($factoryConfig)->build();
    }

    /**
     * Returns default configuration for DTO factory.
     *
     * @param ApiRoutesFactoryConfig $initialFactoryConfig Initial configuration
     *
     * @return ApiRoutesFactoryConfig
     * @throws ConfigurationException
     */
    private function getConfiguration(ApiRoutesFactoryConfig $initialFactoryConfig): ApiRoutesFactoryConfig
    {
        return new ApiRoutesFactoryConfig([
            ApiRoutesFactoryConfig::CONTROLLERS_NAMESPACE =>
                $initialFactoryConfig->controllersNamespace ?? $this->getApiControllersNamespace(),
            ApiRoutesFactoryConfig::RESULT_FILENAME =>
                $initialFactoryConfig->resultFilename ?? $this->getResultFileName(),
            ApiRoutesFactoryConfig::TEMPLATE_FILENAME =>
                $initialFactoryConfig->templateFilename ?? $this->getTemplateFileName(),
            ApiRoutesFactoryConfig::SWAGGER_FILE =>
                $initialFactoryConfig->swaggerFile ?? $this->getSpecificationFileName(),
            ApiRoutesFactoryConfig::SECURITY_SCHEMES_MIDDLEWARES =>
                $initialFactoryConfig->securitySchemesMiddlewares ?? $this->getSecuritySchemesMiddlewares(),
        ]);
    }

    /**
     * Returns API controllers namespace.
     *
     * @return string
     */
    private function getApiControllersNamespace(): string
    {
        return $this->getPackageConfig('api_controllers.namespace');
    }

    /**
     * Returns full path to file with api routes that should be generated.
     *
     * @return string
     */
    private function getResultFileName(): string
    {
        return $this->getServiceConfig('result_file_name');
    }

    /**
     * Returns full path to api specification.
     *
     * @return string
     */
    private function getSpecificationFileName(): string
    {
        return $this->getPackageConfig('swagger.path');
    }

    /**
     * Returns middlewares that should be applied for security schemes.
     *
     * @return array
     */
    private function getSecuritySchemesMiddlewares(): array
    {
        return $this->getServiceConfig('security_schemes_middlewares');
    }
}
