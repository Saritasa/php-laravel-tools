<?php

namespace Saritasa\LaravelTools\Tests;

use Illuminate\Config\Repository;
use PHPUnit\Framework\TestCase;

// TODO declare classes retrieving from here instead of new in each test class
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
                    'generated_controller_suffix' => 'ApiController',
                ],
                'api_routes' => [
                    // Well-known routes which controller, action and route names should not be guessed and used from config
                    'known_routes' => [
                        'GET' => [
                            '/{{resourceName}}' => [
                                'method' => 'index',
                                'name' => '{{resourceName}}.index',
                            ],
                            '/{{resourceName}}/{id}' => [
                                'method' => 'show',
                                'name' => '{{resourceName}}.show',
                            ],
                        ],
                        'POST' => [
                            '/{{resourceName}}' => [
                                'method' => 'store',
                                'name' => '{{resourceName}}.store',
                            ],
                        ],
                        'PUT' => [
                            '/{{resourceName}}/{id}' => [
                                'method' => 'update',
                                'name' => '{{resourceName}}.update',
                            ],
                        ],
                        'DELETE' => [
                            '/{{resourceName}}/{id}' => [
                                'method' => 'destroy',
                                'name' => '{{resourceName}}.destroy',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
