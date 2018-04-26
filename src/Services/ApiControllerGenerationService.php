<?php

namespace Saritasa\LaravelTools\Services;

use Exception;
use Illuminate\Config\Repository;
use Illuminate\Support\Collection;
use Saritasa\Exceptions\ConfigurationException;
use Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\ApiRoutesImplementationGuesser;
use Saritasa\LaravelTools\CodeGenerators\ClassGenerator;
use Saritasa\LaravelTools\DTO\Configs\ApiControllerFactoryConfig;
use Saritasa\LaravelTools\DTO\PhpClasses\ClassObject;
use Saritasa\LaravelTools\DTO\Routes\ApiRouteImplementationObject;
use Saritasa\LaravelTools\Swagger\SwaggerReader;

/**
 * Service that can scaffold controllers and methods that covers api specification.
 */
class ApiControllerGenerationService extends ClassGenerationService
{
    /**
     * Section key in configuration repository where configuration for this service located.
     *
     * @var string
     */
    protected $serviceConfigurationKey = 'api_controllers';

    /**
     * Php class generator. Allows to build class declaration based on class details.
     *
     * @var ClassGenerator
     */
    private $classGenerator;

    /**
     * Api route implementation guesser that can guess which controller, method and name should be used for api route
     * specification.
     *
     * @var ApiRoutesImplementationGuesser
     */
    private $apiRoutesImplementationGuesser;

    /**
     * Swagger specification file reader. Allows to retrieve API specification
     *
     * @var SwaggerReader
     */
    private $swaggerReader;

    /**
     * Service that can scaffold controllers and methods that covers api specification.
     *
     * @param Repository $configRepository Configuration storage
     * @param TemplatesManager $templatesManager Scaffold templates manager. Allows to retrieve full path name to
     *     template
     * @param ClassGenerator $classGenerator Allows to build class declaration based on class details
     * @param ApiRoutesImplementationGuesser $apiRoutesImplementationGuesser Api route implementation guesser that can
     *     guess which controller, method and name should be used for api route specification
     * @param SwaggerReader $swaggerReader Swagger specification file reader. Allows to retrieve API specification
     *
     * @throws ConfigurationException
     */
    public function __construct(
        Repository $configRepository,
        TemplatesManager $templatesManager,
        ClassGenerator $classGenerator,
        ApiRoutesImplementationGuesser $apiRoutesImplementationGuesser,
        SwaggerReader $swaggerReader
    ) {
        parent::__construct($configRepository, $templatesManager);
        $this->classGenerator = $classGenerator;
        $this->apiRoutesImplementationGuesser = $apiRoutesImplementationGuesser;
        $this->swaggerReader = $swaggerReader;
    }

    /**
     * Starts controllers generation based on swagger file specification.
     *
     * @param string $specificationFilename Swagger specification filename
     *
     * @return array List of generated controller names
     * @throws ConfigurationException
     */
    public function generateControllers(string $specificationFilename): array
    {
        $apiPaths = $this->swaggerReader->getApiPaths($specificationFilename);
        $implementations = new Collection([]);
        foreach ($apiPaths as $apiPath) {
            $implementations->push($this->apiRoutesImplementationGuesser->getRouteImplementationDetails($apiPath));
        }

        $implementationsByControllers = $implementations->groupBy(ApiRouteImplementationObject::CONTROLLER);

        $controllerNames = [];

        /**
         * Suggested methods that should be presented ni generated controller.
         *
         * @var Collection $controllerMethodsImplementations
         */
        foreach ($implementationsByControllers as $controllerName => $controllerMethodsImplementations) {
            $controllerNames[] = $this->generateController(
                $controllerName,
                $controllerMethodsImplementations->toArray()
            );
        }

        return $controllerNames;
    }

    /**
     * Returns default configuration for api controller factory.
     *
     * @param string $controllerClassName Result api controller file name
     *
     * @return ApiControllerFactoryConfig
     * @throws ConfigurationException
     */
    private function getConfiguration(string $controllerClassName): ApiControllerFactoryConfig
    {
        return new ApiControllerFactoryConfig([
            ApiControllerFactoryConfig::NAMESPACE => $this->getClassNamespace(),
            ApiControllerFactoryConfig::PARENT_CLASS_NAME => $this->getParentClassName(),
            ApiControllerFactoryConfig::TEMPLATE_FILENAME => $this->getTemplateFileName(),
            ApiControllerFactoryConfig::RESULT_FILENAME => $this->getResultFileName($controllerClassName),
            ApiControllerFactoryConfig::CLASS_NAME => $controllerClassName,
            ApiControllerFactoryConfig::NAMES_SUFFIX => $this->getServiceConfig('name_suffix'),
        ]);
    }

    /**
     * Check whether controller exists or not.
     *
     * @param string $controllerFilename File name to check
     *
     * @return boolean
     */
    private function controllerExists(string $controllerFilename): bool
    {
        return file_exists($controllerFilename);
    }

    /**
     * Generates new controller based on controller name and suggested methods.
     *
     * @param string $controllerName Name of the new api controller cass
     * @param ApiRouteImplementationObject[] $apiRoutesImplementations Suggested methods in the new controller
     *
     * @return string
     * @throws ConfigurationException
     * @throws Exception
     */
    public function generateController(string $controllerName, array $apiRoutesImplementations): string
    {
        $config = $this->getConfiguration($controllerName);

        if ($this->controllerExists($config->resultFilename)) {
            fputs(STDERR, "File {$config->resultFilename} already exists. Skipping...\n");

            return $config->resultFilename;
        }

        $controllerMethods = [];
        foreach ($apiRoutesImplementations as $implementation) {
            if (method_exists($config->parentClassName, $implementation->function->name)) {
                continue;
            }

            $controllerMethods[] = $implementation->function;
        }

        $classObject = new ClassObject([
            ClassObject::NAME => $config->className,
            ClassObject::NAMESPACE => $config->namespace,
            ClassObject::PARENT => $config->parentClassName,
            ClassObject::DESCRIPTION => '',
            ClassObject::PROPERTIES => [],
            ClassObject::PHPDOC_PROPERTIES => [],
            ClassObject::CONSTANTS => [],
            ClassObject::METHODS => $controllerMethods,
        ]);

        $classTemplate = file_get_contents($config->templateFilename);

        $classBody = $this->classGenerator->render($classObject, $classTemplate);

        file_put_contents($config->resultFilename, $classBody);

        return $config->resultFilename;
    }
}
