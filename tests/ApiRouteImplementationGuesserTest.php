<?php

namespace Saritasa\LaravelTools\Tests;

use Saritasa\LaravelTools\DTO\Routes\ApiRouteObject;
use Saritasa\LaravelTools\Services\ApiRoutesImplementationGuesser;

class ApiRouteImplementationGuesserTest extends LaravelToolsTestsHelpers
{
    /**
     * @dataProvider implementationGuesserTestSet
     *
     * @param string $group
     * @param string $method
     * @param string $url
     * @param string $operationId
     * @param string $expectedController
     * @param string $expectedMethod
     * @param string $expectedName
     *
     * @return void
     */
    public function testGuessMethod(
        string $group,
        string $method,
        string $url,
        string $operationId,
        string $expectedController,
        string $expectedMethod,
        string $expectedName
    ): void {

        $configRepository = $this->getConfigRepository();
        $configRepository->set('laravel_tools.api_routes.known_routes.POST', [
            '/auth' => [
                'controller' => 'AuthApiController',
                'action' => 'login',
                'name' => 'login',
            ],
            '/auth/password/reset' => [
                'controller' => 'ForgotPasswordApiController',
                'action' => 'sendResetLinkEmail',
                'name' => 'password.sendResetLink',
            ],
        ]);
        $routeGuesser = new ApiRoutesImplementationGuesser($configRepository);

        $route = new ApiRouteObject([
            ApiRouteObject::GROUP => $group,
            ApiRouteObject::METHOD => $method,
            ApiRouteObject::URL => $url,
            ApiRouteObject::OPERATION_ID => $operationId,
            // Not used by guesser yet
            ApiRouteObject::SECURITY_SCHEME => null,
            ApiRouteObject::DESCRIPTION => null,
        ]);

        $actual = $routeGuesser->getRouteImplementationDetails($route);

        $this->assertEquals($expectedController, $actual->controller);
        $this->assertEquals($expectedMethod, $actual->action);
        $this->assertEquals($expectedName, $actual->name);
    }

    public function implementationGuesserTestSet(): array
    {
        return [
            'simple with known REST' => [
                'Users',
                'GET',
                '/users',
                'GetUsers',
                'UsersApiController',
                'index',
                'users.index',
            ],
            'guessed' => [
                'Jobs',
                'GET',
                '/jobs/active',
                'GetActiveJobs',
                'JobsApiController',
                'getActiveJobs',
                'jobs.getActiveJobs',
            ],
            'simple with known custom' => [
                'Authentication',
                'POST',
                '/auth',
                'Authenticate',
                'AuthApiController',
                'login',
                'login',
            ],
            'simple with known custom 2' => [
                'Authentication',
                'POST',
                '/auth/password/reset',
                'Reset',
                'ForgotPasswordApiController',
                'sendResetLinkEmail',
                'password.sendResetLink',
            ],
            'unknown without operationId' => [
                'Users',
                'GET',
                'users/{id}/roles',
                '',
                'UsersApiController',
                'getUserRoles',
                'users.getUserRoles',
            ],
            'group and resource mismatch' => [
                'Users',
                'GET',
                'contractors/{id}/roles',
                '',
                'UsersApiController',
                'getContractorRoles',
                'users.getContractorRoles',
            ],
            'group and resource mismatch REST' => [
                'Users',
                'GET',
                'contractors',
                '',
                'UsersApiController',
                'getContractors',
                'users.getContractors',
            ],
            'group and resource mismatch REST 2' => [
                'Users',
                'GET',
                'contractors/{id}',
                '',
                'UsersApiController',
                'getContractor',
                'users.getContractor',
            ],
        ];
    }
}
