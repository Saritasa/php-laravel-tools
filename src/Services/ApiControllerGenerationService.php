<?php

namespace Saritasa\LaravelTools\Services;

use Exception;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Saritasa\Exceptions\ConfigurationException;
use Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\ApiRoutesImplementationGuesser;
use Saritasa\LaravelTools\CodeGenerators\ClassGenerator;
use Saritasa\LaravelTools\DTO\Configs\ApiControllerFactoryConfig;
use Saritasa\LaravelTools\DTO\PhpClasses\ClassObject;
use Saritasa\LaravelTools\DTO\PhpClasses\ClassPropertyObject;
use Saritasa\LaravelTools\DTO\Routes\ApiRouteImplementationObject;
use Saritasa\LaravelTools\Swagger\SwaggerReader;
use UnexpectedValueException;

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
     * Files storage.
     *
     * @var Filesystem
     */
    private $filesystem;

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
     * @param Filesystem $filesystem Files storage
     *
     * @throws ConfigurationException
     */
    public function __construct(
        Repository $configRepository,
        TemplatesManager $templatesManager,
        ClassGenerator $classGenerator,
        ApiRoutesImplementationGuesser $apiRoutesImplementationGuesser,
        SwaggerReader $swaggerReader,
        Filesystem $filesystem
    ) {
        parent::__construct($configRepository, $templatesManager);
        $this->classGenerator = $classGenerator;
        $this->apiRoutesImplementationGuesser = $apiRoutesImplementationGuesser;
        $this->swaggerReader = $swaggerReader;
        $this->filesystem = $filesystem;
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
     * Fill placeholders in passed array values.
     *
     * @param array $values Values to replace placeholder in
     * @param array $placeholders Placeholders values
     *
     * @return array
     * @throws UnexpectedValueException when placeholder value is empty
     */
    private function fillPlaceholders(array $values, array $placeholders): array
    {
        foreach ($values as $key => $value) {
            foreach ($placeholders as $placeholder => $placeholderValue) {
                if (strpos($value, $placeholder) !== false) {
                    if (!$placeholderValue) {
                        throw new UnexpectedValueException("Placeholder {$placeholder} is empty.");
                    }

                    $values[$key] = str_replace(
                        $placeholder,
                        $placeholderValue,
                        $value
                    );
                }
            }
        }

        return $values;
    }

    /**
     * Returns list of class properties that should be added into generated api controller.
     *
     * @param array $placeholders Placeholders to fill custom class properties
     *
     * @return ClassPropertyObject[]
     */
    private function getCustomProperties(array $placeholders): array
    {
        $configCustomProperties = $this->getServiceConfig('custom_properties');
        $customProperties = [];
        foreach ($configCustomProperties as $configCustomProperty) {
            try {
                $configCustomProperty = $this->fillPlaceholders($configCustomProperty, $placeholders);
            } catch (UnexpectedValueException $e) {
                fputs(STDERR, $e->getMessage() . ' Skipping property...');
                continue;
            }

            $customProperties[] = new ClassPropertyObject($configCustomProperty);
        }

        return $customProperties;
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
    private function generateController(string $controllerName, array $apiRoutesImplementations): string
    {
        $config = $this->getConfiguration($controllerName);

        if ($this->controllerExists($config->resultFilename)) {
            fputs(STDERR, "File {$config->resultFilename} already exists. Skipping...\n");

            return $config->resultFilename;
        }

        $controllerMethods = [];
        $resourceClass = null;
        foreach ($apiRoutesImplementations as $implementation) {
            $resourceClass = $resourceClass ?? $implementation->resourceClass;
            if (method_exists($config->parentClassName, $implementation->function->name)) {
                continue;
            }

            $controllerMethods[] = $implementation->function;
        }

        $classProperties = $this->getCustomProperties(['{{resourceClass}}' => $resourceClass]);
        $description = str_replace('_', ' ', Str::snake($controllerName));

        $classObject = new ClassObject([
            ClassObject::NAME => $config->className,
            ClassObject::NAMESPACE => $config->namespace,
            ClassObject::PARENT => $config->parentClassName,
            ClassObject::DESCRIPTION => $description,
            ClassObject::PROPERTIES => $classProperties,
            ClassObject::PHPDOC_PROPERTIES => [],
            ClassObject::CONSTANTS => [],
            ClassObject::METHODS => $controllerMethods,
        ]);

        $classTemplate = $this->filesystem->get($config->templateFilename);

        $classBody = $this->classGenerator->render($classObject, $classTemplate);

        $this->filesystem->put($config->resultFilename, $classBody);

        return $config->resultFilename;
    }
}
