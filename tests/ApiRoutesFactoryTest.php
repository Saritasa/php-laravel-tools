<?php

namespace Saritasa\LaravelTools\Tests;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Saritasa\Exceptions\ConfigurationException;
use Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\ApiRoutesBlockGenerator;
use Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\ApiRoutesGenerator;
use Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\ApiRoutesGroupGenerator;
use Saritasa\LaravelTools\CodeGenerators\CodeFormatter;
use Saritasa\LaravelTools\CodeGenerators\CommentsGenerator;
use Saritasa\LaravelTools\DTO\Configs\ApiRoutesFactoryConfig;
use Saritasa\LaravelTools\Factories\ApiRoutesDeclarationFactory;
use Saritasa\LaravelTools\Services\TemplateWriter;

class ApiRoutesFactoryTest extends LaravelToolsTestsHelpers
{
    private $resultFileName = 'apiFactoryUnitTestsResult.php';

    protected function tearDown()
    {
        parent::tearDown();
        if (file_exists($this->resultFileName)) {
            unlink($this->resultFileName);
        }
    }

    /**
     * Generates test configuration of api route factory.
     *
     * @param string $swaggerExample Which swagger example file should be taken
     *
     * @return ApiRoutesFactoryConfig
     */
    private function getFactoryConfig(string $swaggerExample): ApiRoutesFactoryConfig
    {
        return new ApiRoutesFactoryConfig([
            ApiRoutesFactoryConfig::SECURITY_SCHEMES_MIDDLEWARES => [
                'NotExistingInSwaggerScheme' => 'token.auth',
                'AuthToken' => 'jwt.auth',
            ],
            ApiRoutesFactoryConfig::ROOT_GROUP_MIDDLEWARES => ['bindings'],
            ApiRoutesFactoryConfig::SWAGGER_FILE => __DIR__ . "/swagger/{$swaggerExample}.yaml",
            ApiRoutesFactoryConfig::CONTROLLERS_NAMESPACE => 'App\Http\Controllers\Api',
            ApiRoutesFactoryConfig::TEMPLATE_FILENAME => __DIR__ . '/swagger/template',
            ApiRoutesFactoryConfig::RESULT_FILENAME => $this->resultFileName,
        ]);
    }

    /**
     * Initializes Api routes factory.
     *
     * @return ApiRoutesDeclarationFactory
     */
    private function getFactory(): ApiRoutesDeclarationFactory
    {
        $codeFormatter = new CodeFormatter($this->getConfigRepository());
        $templateWriter = new TemplateWriter(app(Filesystem::class));
        $commentsGenerator = new CommentsGenerator();
        $swaggerReader = $this->getSwaggerReader();
        $apiRouteGenerator = $this->getApiRouteGenerator();
        $apiRoutesGroupGenerator = new ApiRoutesGroupGenerator($codeFormatter, $commentsGenerator);
        $apiRoutesBlockGenerator = new ApiRoutesBlockGenerator($codeFormatter, $commentsGenerator, $apiRouteGenerator);
        $apiRoutesGenerator = new ApiRoutesGenerator(
            $apiRouteGenerator,
            $apiRoutesBlockGenerator,
            $apiRoutesGroupGenerator
        );

        return new ApiRoutesDeclarationFactory(
            $templateWriter,
            $codeFormatter,
            $commentsGenerator,
            $swaggerReader,
            $apiRoutesGenerator
        );
    }

    /**
     * Test build method.
     *
     * @dataProvider factoryBuildMethodTestSet
     *
     * @param string $swaggerExample Which swagger example file should be taken
     *
     * @return void
     * @throws ConfigurationException
     * @throws FileNotFoundException
     */
    public function testBuildMethod(string $swaggerExample)
    {
        $factoryConfig = $this->getFactoryConfig($swaggerExample);
        $factory = $this->getFactory();

        $resultFileName = $factory->configure($factoryConfig)->build();
        $resultFile = file_get_contents($resultFileName);
        $expectedContent = file_get_contents(__DIR__ . "/swagger/{$swaggerExample}.php");

        $this->assertEquals($factoryConfig->resultFilename, $resultFileName);
        $this->assertNotEmpty($expectedContent);
        $this->assertNotEmpty($resultFile);
        $this->assertEquals($expectedContent, $resultFile);
    }

    public function factoryBuildMethodTestSet(): array
    {
        return [
            'simplest swagger file' => ['minimal'],
            'swagger file with delete method' => ['extended'],
            'swagger file with security scheme' => ['secure'],
            'swagger file with security scheme and reach descriptions' => ['secureReach'],
        ];
    }
}
