<?php

namespace Saritasa\LaravelTools\Services;

use Exception;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Saritasa\LaravelTools\DTO\ApiRoutesFactoryConfig;
use Saritasa\LaravelTools\Enums\ScaffoldTemplates;
use Saritasa\LaravelTools\Factories\ApiRoutesDeclarationFactory;

/**
 * Generates api routes definition based on swagger specification.
 */
class ApiRoutesService
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
     */
    public function __construct(
        Repository $configRepository,
        TemplatesManager $templatesManager,
        ApiRoutesDeclarationFactory $apiRoutesDeclarationFactory
    ) {
        $this->configRepository = $configRepository;
        $this->templatesManager = $templatesManager;
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
        return $this->configRepository->get('laravel_tools.api_controllers.namespace');
    }

    /**
     * Returns full path to file with api routes that should be generated.
     *
     * @return string
     */
    private function getResultFileName(): string
    {
        return $this->configRepository->get('laravel_tools.api_routes.result_file_name');
    }

    /**
     * Returns full path to api specification.
     *
     * @return string
     */
    private function getSpecificationFileName(): string
    {
        return $this->configRepository->get('laravel_tools.swagger.path');
    }

    /**
     * Returns middlewares that should be applied for security schemes.
     *
     * @return array
     */
    private function getSecuritySchemesMiddlewares(): array
    {
        return $this->configRepository->get('laravel_tools.api_routes.security_schemes_middlewares');
    }

    /**
     * Returns api routes template file name.
     *
     * @return string
     */
    private function getTemplateFileName(): string
    {
        $templateFileName = $this->configRepository->get(
            'laravel_tools.api_routes.template_file_name',
            ScaffoldTemplates::API_ROUTES_TEMPLATE
        );

        return $this->templatesManager->getTemplatePath($templateFileName);
    }
}
