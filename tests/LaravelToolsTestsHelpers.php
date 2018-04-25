<?php

namespace Saritasa\LaravelTools\Tests;

use Illuminate\Config\Repository;
use PHPUnit\Framework\TestCase;

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
}
