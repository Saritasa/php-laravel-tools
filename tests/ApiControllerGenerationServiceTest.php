<?php

namespace Saritasa\LaravelTools\Tests;

use Saritasa\Exceptions\ConfigurationException;
use Saritasa\LaravelTools\Services\ApiRoutesImplementationGuesser;
use Saritasa\LaravelTools\Services\GenerationServices\ApiControllerGenerationService;
use Saritasa\LaravelTools\Services\TemplatesManager;

class ApiControllerGenerationServiceTest extends LaravelToolsTestsHelpers
{
    /**
     * @dataProvider factoryBuildMethodTestSet
     *
     * @param string $swaggerExample Which swagger example file should be taken
     * @param array $expectedGeneratedClasses
     * @param bool $useModelsBinding
     * @param string $nameSuffix
     *
     * @throws ConfigurationException
     */
    public function testBuildMethod(
        string $swaggerExample,
        array $expectedGeneratedClasses,
        bool $useModelsBinding,
        string $nameSuffix
    ) {
        $configRepository = $this->getConfigRepository();
        $configRepository->set('laravel_tools.models.namespace', '');
        $configRepository->set('laravel_tools.api_controllers.name_suffix', $nameSuffix);
        if ($useModelsBinding) {
            $configRepository->set('laravel_tools.swagger', [
                'path_parameters_substitutions' => [
                    // Swagger path parameter 'id' should be renamed to 'model' with typehinting of resource class:
                    'id' => [
                        'name' => 'model',
                        'type' => '{{resourceClass}}',
                        'description' => 'Related resource model',
                    ],
                ],
            ]);
        }
        $apiControllerGenerationService = new ApiControllerGenerationService(
            $configRepository,
            new TemplatesManager(),
            $this->getClassGenerator(),
            new ApiRoutesImplementationGuesser($configRepository),
            $this->getSwaggerReader(),
            $this->getFileSystem()
        );

        $generatedClasses = $apiControllerGenerationService
            ->generateControllers(__DIR__ . "/swagger/{$swaggerExample}.yaml");

        $this->assertEquals(sort($expectedGeneratedClasses), sort($generatedClasses));

        foreach ($generatedClasses as $index => $generatedClass) {
            $this->assertEquals(
                file_get_contents($expectedGeneratedClasses[$index]),
                file_get_contents($generatedClass)
            );
            unlink($generatedClass);
        }
    }

    public function factoryBuildMethodTestSet(): array
    {
        return [
            'swagger file with security scheme and reach descriptions' => [
                'secureReach',
                [
                    __DIR__ . DIRECTORY_SEPARATOR . 'swagger/PetsTestController.php',
                    __DIR__ . DIRECTORY_SEPARATOR . 'swagger/UsersTestController.php',
                ],
                false,
                'TestController',
            ],
            'swagger file with security scheme and reach descriptions, Controllers with models bindings' => [
                'secureReach',
                [
                    __DIR__ . DIRECTORY_SEPARATOR . 'swagger/PetsTestBindingsController.php',
                    __DIR__ . DIRECTORY_SEPARATOR . 'swagger/UsersTestBindingsController.php',
                ],
                true,
                'TestBindingsController',
            ],
        ];
    }
}
