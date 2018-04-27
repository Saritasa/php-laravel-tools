<?php

namespace Saritasa\LaravelTools\Tests;

use Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\ApiRoutesBlockGenerator;
use Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\ApiRoutesGroupGenerator;
use Saritasa\LaravelTools\CodeGenerators\CodeFormatter;
use Saritasa\LaravelTools\CodeGenerators\CommentsGenerator;
use Saritasa\LaravelTools\DTO\Routes\ApiRouteObject;
use Saritasa\LaravelTools\Enums\HttpMethods;

class RouteGeneratorsTest extends LaravelToolsTestsHelpers
{
    /**
     * @dataProvider apiResourceRouteGeneratorTestSet
     *
     * @param null|string $description Route description
     * @param string $method
     * @param string $path
     * @param string $expected
     *
     * @return void
     */
    public function testApiResourceRouteGenerator(
        ?string $description,
        string $method,
        string $path,
        string $expected
    ): void {
        $apiRouteGenerator = $this->getApiResourceRegistrarRouteGenerator();
        $route = new ApiRouteObject([
            ApiRouteObject::DESCRIPTION => $description,
            ApiRouteObject::GROUP => 'Users',
            ApiRouteObject::METHOD => $method,
            ApiRouteObject::URL => $path,
        ]);

        $actual = $apiRouteGenerator->render($route);
        $this->assertEquals($expected, $actual);
    }

    public function apiResourceRouteGeneratorTestSet(): array
    {
        return [
            'GET route' => [
                'Get list of users',
                HttpMethods::GET,
                '/users',
                "// Get list of users\n\$registrar->get('/users', UsersApiController::class, 'index', 'users.index');",
            ],
            'PUT route' => [
                'Update user',
                HttpMethods::PUT,
                '/users/{id}',
                "// Update user\n\$registrar->put('/users/{id}', UsersApiController::class, 'update', 'users.update');",
            ],
            'GET route with param' => [
                'Show user',
                HttpMethods::GET,
                '/users/{id}',
                "// Show user\n\$registrar->get('/users/{id}', UsersApiController::class, 'show', 'users.show');",
            ],
            'POST route' => [
                'Create new user',
                HttpMethods::POST,
                '/users',
                "// Create new user\n\$registrar->post('/users', UsersApiController::class, 'store', 'users.store');",
            ],
            'Without description' => [
                '',
                HttpMethods::GET,
                '/users',
                "\$registrar->get('/users', UsersApiController::class, 'index', 'users.index');",
            ],
        ];
    }

    /**
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
        $apiRouteGenerator = $this->getApiRouteGenerator();
        $route = new ApiRouteObject([
            ApiRouteObject::DESCRIPTION => $description,
            ApiRouteObject::GROUP => 'Users',
            ApiRouteObject::METHOD => $method,
            ApiRouteObject::URL => $path,
        ]);

        $actual = $apiRouteGenerator->render($route);
        $this->assertEquals($expected, $actual);
    }

    public function routeGeneratorTestSet(): array
    {
        return [
            'GET route' => [
                'Get list of users',
                HttpMethods::GET,
                '/users',
                "// Get list of users\n\$api->get('/users', 'UsersApiController@index')->name('users.index');",
            ],
            'PUT route' => [
                'Update user',
                HttpMethods::PUT,
                '/users/{id}',
                "// Update user\n\$api->put('/users/{id}', 'UsersApiController@update')->name('users.update');",
            ],
            'GET route with param' => [
                'Show user',
                HttpMethods::GET,
                '/users/{id}',
                "// Show user\n\$api->get('/users/{id}', 'UsersApiController@show')->name('users.show');",
            ],
            'POST route' => [
                'Create new user',
                HttpMethods::POST,
                '/users',
                "// Create new user\n\$api->post('/users', 'UsersApiController@store')->name('users.store');",
            ],
            'Without description' => [
                '',
                HttpMethods::GET,
                '/users',
                "\$api->get('/users', 'UsersApiController@index')->name('users.index');",
            ],
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
        $codeFormatter = new CodeFormatter($this->getConfigRepository());
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
            new CodeFormatter($this->getConfigRepository()),
            new CommentsGenerator(),
            $this->getApiRouteGenerator()
        );

        $routes = [
            new ApiRouteObject([
                ApiRouteObject::DESCRIPTION => 'Get users',
                ApiRouteObject::GROUP => 'Users',
                ApiRouteObject::METHOD => 'GET',
                ApiRouteObject::URL => '/users',
            ]),
            new ApiRouteObject([
                ApiRouteObject::DESCRIPTION => 'Get user',
                ApiRouteObject::GROUP => 'Users',
                ApiRouteObject::METHOD => 'GET',
                ApiRouteObject::URL => '/users/{id}',
            ]),
        ];
        $expected = <<<'EXPECTED'
// Get users
$api->get('/users', 'UsersApiController@index')->name('users.index');
// Get user
$api->get('/users/{id}', 'UsersApiController@show')->name('users.show');
EXPECTED;

        $actual = $routesBlockGenerator->render($routes);
        $this->assertEquals($expected, $actual);

        $expectedWithDescription = <<<'EXPECTED'
////////////////////////////
// User management routes //
////////////////////////////

// Get users
$api->get('/users', 'UsersApiController@index')->name('users.index');
// Get user
$api->get('/users/{id}', 'UsersApiController@show')->name('users.show');
EXPECTED;

        $actual = $routesBlockGenerator->render($routes, 'User management routes');
        $this->assertEquals($expectedWithDescription, $actual);
    }
}
