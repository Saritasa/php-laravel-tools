<?php

namespace Saritasa\LaravelTools\Tests;

use Illuminate\Config\Repository;
use PHPUnit\Framework\TestCase;
use Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\ApiRouteGenerator;
use Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\ApiRoutesBlockGenerator;
use Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\ApiRoutesGroupGenerator;
use Saritasa\LaravelTools\CodeGenerators\CodeFormatter;
use Saritasa\LaravelTools\CodeGenerators\CommentsGenerator;
use Saritasa\LaravelTools\DTO\ApiRouteObject;
use Saritasa\LaravelTools\Enums\HttpMethods;

class RouteGeneratorsTest extends TestCase
{
    /**
     *
     * @dataProvider routeGeneratorTestSet
     *
     * @param null|string $description Route description
     * @param string $method
     * @param string $path
     * @param string $expected
     *
     * @return void
     */
    public function testRouteGenerator(?string $description, string $method, string $path, string $expected): void
    {
        $routeGenerator = new ApiRouteGenerator();
        $route = new ApiRouteObject([
            ApiRouteObject::DESCRIPTION => $description,
            ApiRouteObject::METHOD => $method,
            ApiRouteObject::URL => $path,
        ]);

        $actual = $routeGenerator->render($route);
        $this->assertEquals($expected, $actual);
    }

    public function routeGeneratorTestSet(): array
    {
        return [
            'GET route' => [
                'Get list of users',
                HttpMethods::GET,
                '/users',
                "// Get list of users\n\$api->get('/users', '')->name('');",
            ],
            'POST route' => [
                'Create new user',
                HttpMethods::POST,
                '/users',
                "// Create new user\n\$api->post('/users', '')->name('');",
            ],
            'Without description' => ['', HttpMethods::GET, '/users', "\$api->get('/users', '')->name('');"],
        ];
    }

    /**
     * @dataProvider routeGroupGeneratorTestSet
     *
     * @param string $groupContent
     * @param array|null $middleware
     * @param null|string $description
     * @param string $expected
     *
     * @return void
     */
    public function testRouteGroupGenerator(
        string $groupContent,
        ?array $middleware,
        ?string $description,
        string $expected
    ): void {
        $configsRepository = new Repository(['laravel_tools.code_style.indent' => '    ']);
        $codeFormatter = new CodeFormatter($configsRepository);
        $commentsGenerator = new CommentsGenerator();
        $routeGroupGenerator = new ApiRoutesGroupGenerator($codeFormatter, $commentsGenerator);

        $actual = $routeGroupGenerator->render($groupContent, $middleware, $description);
        $this->assertEquals($expected, $actual);
    }

    public function routeGroupGeneratorTestSet(): array
    {
        return [
            'simple' => [
                "\$api->get('/users', '');",
                [],
                '',
                "\$api->group([], function (Router \$api) {\n    \$api->get('/users', '');\n});",
            ],
            'with description' => [
                "\$api->get('/users', '');",
                [],
                'Users routes group',
                "// Users routes group\n\$api->group([], function (Router \$api) {\n    \$api->get('/users', '');\n});",
            ],
            'with few lines' => [
                "\$api->get('/users', '');\n\$api->get('/users/{id}', '');",
                [],
                '',
                "\$api->group([], function (Router \$api) {\n    \$api->get('/users', '');\n    \$api->get('/users/{id}', '');\n});",
            ],

            'with middleware' => [
                "\$api->get('/users', '');",
                ['api.auth', 'bindings'],
                '',
                "\$api->group(['middleware' => ['api.auth', 'bindings']], function (Router \$api) {\n    \$api->get('/users', '');\n});",
            ],
        ];
    }

    public function testRoutesBlockGenerator(): void
    {
        $routesBlockGenerator = new ApiRoutesBlockGenerator(
            new CodeFormatter(new Repository()),
            new CommentsGenerator(),
            new ApiRouteGenerator()
        );

        $routes = [
            new ApiRouteObject([
                ApiRouteObject::DESCRIPTION => 'Get users',
                ApiRouteObject::METHOD => 'GET',
                ApiRouteObject::URL => 'users',
            ]),
            new ApiRouteObject([
                ApiRouteObject::DESCRIPTION => 'Get user',
                ApiRouteObject::METHOD => 'GET',
                ApiRouteObject::URL => 'users/{id}',
            ]),
        ];
        $expected = <<<'EXPECTED'
// Get users
$api->get('users', '')->name('');
// Get user
$api->get('users/{id}', '')->name('');
EXPECTED;

        $actual = $routesBlockGenerator->render($routes);
        $this->assertEquals($expected, $actual);

        $expectedWithDescription = <<<'EXPECTED'
////////////////////////////
// User management routes //
////////////////////////////

// Get users
$api->get('users', '')->name('');
// Get user
$api->get('users/{id}', '')->name('');
EXPECTED;

        $actual = $routesBlockGenerator->render($routes, 'User management routes');
        $this->assertEquals($expectedWithDescription, $actual);
    }
}
