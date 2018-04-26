<?php

namespace Saritasa\LaravelTools\Tests;

use Illuminate\Config\Repository;
use PHPUnit\Framework\TestCase;
use Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\ApiRouteGenerator;
use Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\ApiRoutesImplementationGuesser;
use Saritasa\LaravelTools\CodeGenerators\ClassPropertyGenerator;
use Saritasa\LaravelTools\CodeGenerators\CodeFormatter;
use Saritasa\LaravelTools\CodeGenerators\CommentsGenerator;
use Saritasa\LaravelTools\CodeGenerators\FunctionGenerator;
use Saritasa\LaravelTools\CodeGenerators\NamespaceExtractor;
use Saritasa\LaravelTools\CodeGenerators\PhpDoc\PhpDocClassDescriptionBuilder;
use Saritasa\LaravelTools\CodeGenerators\PhpDoc\PhpDocMethodParameterDescriptionBuilder;
use Saritasa\LaravelTools\CodeGenerators\PhpDoc\PhpDocSingleLinePropertyDescriptionBuilder;
use Saritasa\LaravelTools\Mappings\PhpToPhpDocTypeMapper;
use Saritasa\LaravelTools\Mappings\SwaggerToPhpTypeMapper;
use Saritasa\LaravelTools\Swagger\SwaggerReader;
use WakeOnWeb\Component\Swagger\Loader\JsonLoader;
use WakeOnWeb\Component\Swagger\Loader\YamlLoader;
use WakeOnWeb\Component\Swagger\SwaggerFactory;

abstract class LaravelToolsTestsHelpers extends TestCase
{
    /**
     * Returns laravel-tools config repository.
     *
     * @return Repository
     */
    protected function getConfigRepository(): Repository
    {
        return new Repository([
            'laravel_tools' => [
                'api_controllers' => [
                    // The generated controller name suffix
                    'name_suffix' => 'ApiController',
                ],
                'api_routes' => [
                    // Well-known routes which controller, action and route names should not be guessed and used from config
                    'known_routes' => [
                        'GET' => [
                            '/{{resourceName}}' => [
                                'action' => 'index',
                                'name' => '{{resourceName}}.index',
                            ],
                            '/{{resourceName}}/{id}' => [
                                'action' => 'show',
                                'name' => '{{resourceName}}.show',
                            ],
                        ],
                        'POST' => [
                            '/{{resourceName}}' => [
                                'action' => 'store',
                                'name' => '{{resourceName}}.store',
                            ],
                        ],
                        'PUT' => [
                            '/{{resourceName}}/{id}' => [
                                'action' => 'update',
                                'name' => '{{resourceName}}.update',
                            ],
                        ],
                        'DELETE' => [
                            '/{{resourceName}}/{id}' => [
                                'action' => 'destroy',
                                'name' => '{{resourceName}}.destroy',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    protected function getSwaggerReader(): SwaggerReader
    {
        return new SwaggerReader(
            new SwaggerFactory(),
            new YamlLoader(),
            new JsonLoader(),
            new SwaggerToPhpTypeMapper()
        );
    }

    protected function getPhpDocClassDescriptionBuilder(): PhpDocClassDescriptionBuilder
    {
        return new PhpDocClassDescriptionBuilder(
            new PhpDocSingleLinePropertyDescriptionBuilder(
                new PhpToPhpDocTypeMapper()
            ),
            $this->getCommentsGenerator(),
            $this->getCodeFormatter()
        );
    }

    protected function getCommentsGenerator(): CommentsGenerator
    {
        return new CommentsGenerator();
    }

    protected function getCodeFormatter(): CodeFormatter
    {
        return new CodeFormatter($this->getConfigRepository());
    }

    protected function getClassPropertyGenerator(): ClassPropertyGenerator
    {
        return new ClassPropertyGenerator(
            new PhpToPhpDocTypeMapper(),
            $this->getCommentsGenerator(),
            $this->getCodeFormatter()
        );
    }

    protected function getFunctionGenerator(): FunctionGenerator
    {
        return new FunctionGenerator(
            $this->getCodeFormatter(),
            $this->getCommentsGenerator(),
            new PhpToPhpDocTypeMapper(),
            new PhpDocMethodParameterDescriptionBuilder(new PhpToPhpDocTypeMapper())
        );
    }

    protected function getNamespaceExtractor(): NamespaceExtractor
    {
        return new NamespaceExtractor($this->getCodeFormatter());
    }

    public function getApiRouteGenerator(): ApiRouteGenerator
    {
        return new ApiRouteGenerator(
            new ApiRoutesImplementationGuesser($this->getConfigRepository()),
            $this->getCommentsGenerator(),
            $this->getCodeFormatter()
        );
    }
}
