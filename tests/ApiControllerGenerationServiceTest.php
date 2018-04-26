<?php

namespace Saritasa\LaravelTools\Tests;

use Saritasa\Exceptions\ConfigurationException;
use Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\ApiRoutesImplementationGuesser;
use Saritasa\LaravelTools\Services\ApiControllerGenerationService;
use Saritasa\LaravelTools\Services\TemplatesManager;

class ApiControllerGenerationServiceTest extends LaravelToolsTestsHelpers
{
    private $resultFileName = 'apiControllerGenerationUnitTestsResult.php';

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        // Create alias for stub models to be detectable by class_exist() function
        class_alias(static::class, 'Pet');
        class_alias(static::class, 'User');
    }

    protected function tearDown()
    {
        parent::tearDown();
        if (file_exists($this->resultFileName)) {
            unlink($this->resultFileName);
        }
    }

    /**
     * @dataProvider factoryBuildMethodTestSet
     *
     * @param string $swaggerExample Which swagger example file should be taken
     *
     * @param array $expectedGeneratedClasses
     *
     * @throws ConfigurationException
     */
    public function testBuildMethod(string $swaggerExample, array $expectedGeneratedClasses)
    {
        $configRepository = $this->getConfigRepository();
        $configRepository->set('laravel_tools.models.namespace', '');
        $configRepository->set('laravel_tools.api_controllers.name_suffix', 'TestController');
        $apiControllerGenerationService = new ApiControllerGenerationService(
            $configRepository,
            new TemplatesManager(),
            $this->getClassGenerator(),
            new ApiRoutesImplementationGuesser($configRepository),
            $this->getSwaggerReader()
        );

        $generatedClasses = $apiControllerGenerationService
            ->generateControllers(__DIR__ . "/swagger/{$swaggerExample}.yaml");

        $this->assertEquals(sort($expectedGeneratedClasses), sort($generatedClasses));

        foreach ($generatedClasses as $index => $generatedClass) {
            $this->assertEquals(
                file_get_contents($generatedClass),
                file_get_contents($expectedGeneratedClasses[$index])
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
                    __DIR__ . DIRECTORY_SEPARATOR . 'PetsTestController.php',
                    __DIR__ . DIRECTORY_SEPARATOR . 'UsersTestController.php',
                ],
            ],
        ];
    }
}
