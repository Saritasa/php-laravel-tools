<?php

return [
    // Application models configuration
    'models' => [
        // Path where models located
        'path' => app_path('Models'),

        // Models namespace
        'namespace' => 'App\Models',

        // Suggest that model contain constants with attribute names (like const FIRST_NAME = 'first_name')
        'suggest_attribute_names_constants' => true,
    ],

    // Form requests configuration
    'form_requests' => [
        // Path where form requests located
        'path' => app_path('Http/Requests'),

        // Form requests namespace
        'namespace' => 'App\Http\Requests',

        // Form requests parent class FQN
        'parent' => \Illuminate\Foundation\Http\FormRequest::class,

        // Form request class template. If template name is just a string than template from package will be taken.
        // If path passed then file by this path will be taken
        'template_file_name' => \Saritasa\LaravelTools\Enums\ScaffoldTemplates::FORM_REQUEST_TEMPLATE,

        // Attributes that should not be taken into account
        'except' => [
            'id',
            'created_at',
            'updated_at',
            'deleted_at',
        ],
    ],

    // Validation rules configuration
    'rules' => [
        // Validation rules dictionary
        'dictionary' => \Saritasa\LaravelTools\Rules\StringValidationRulesDictionary::class,
        // 'dictionary' => \Saritasa\LaravelTools\Rules\FluentValidationRulesDictionary::class
    ],

    // Data Transfer Objects configuration
    'dto' => [
        // Path where DTOs are located
        'path' => app_path('Models/Dto'),

        // DTO classes namespace
        'namespace' => 'App\Models\Dto',

        // DTO parent class FQN
        'parent' => \Saritasa\Dto::class,

        // DTO class template. If template name is just a string than template from package will be taken.
        // If path passed then file by this path will be taken
        'template_file_name' => \Saritasa\LaravelTools\Enums\ScaffoldTemplates::DTO_TEMPLATE,

        // Whether constants block with attributes names should be generated
        'with_constants' => false,

        // Whether generated DTO be with protected properties or not
        'immutable' => false,

        // Whether generated DTO be with typehinted getters and setters
        'strict' => false,

        // Immutable DTO parent class FQN in case you need immutable DTO
        'immutable_parent' => \Saritasa\Dto::class,

        // Strict-typed DTO parent class FQN in case you need DTO with strong attribute types
        'strict_type_parent' => \Saritasa\Dto::class,

        // Strict-typed DTO parent class FQN in case you need immutable DTO with strong attribute types
        'immutable_strict_type_parent' => \Saritasa\Dto::class,

        // Attributes that should not be taken into account
        'except' => [
            'deleted_at',
        ],
    ],

    // Code style configuration
    'code_style' => [
        // Code indent that should be used for padding
        'indent' => '    ',
    ],

    // Swagger configuration
    'swagger' => [
        // Swagger file location
        'path' => 'Artifacts/API/swagger.yaml',
    ],

    // Api controllers configuration
    'api_controllers' => [
        // Path where API controllers are located
        'path' => app_path('Http/Controllers/Api'),

        // API controllers namespace
        'namespace' => 'App\Http\Controllers\Api',

        // Api controllers parent class FQN
        'parent' => 'AppApiController',

        // Api controller class template. If template name is just a string than template from package will be taken.
        // If path passed then file by this path will be taken
        'template_file_name' => \Saritasa\LaravelTools\Enums\ScaffoldTemplates::API_CONTROLLER_TEMPLATE,

        // The generated controller name suffix
        'name_suffix' => 'ApiController',

        // Custom properties that will be added to generated API controller class.
        // Values should match ClassPropertyObject::class structure.
        // available placeholders for values is :
        // - {{resourceClass}} - FQN of guessed resource class that is handled by controller.
        // When placeholder value is empty then property will be ignored
        'custom_properties' => [
            [
                'name' => 'modelsClass',
                'type' => '{{resourceClass}}',
                'value' => '{{resourceClass}}::class',
                'description' => 'Resource class that handled by this API controller',
                'visibilityType' => 'protected',
            ],
        ],
    ],

    // Api routes configuration
    'api_routes' => [
        // Template of the api.php file that will be generated. If template name is just a string than template from package will be taken.
        // If path passed then file by this path will be taken
        'template_file_name' => \Saritasa\LaravelTools\Enums\ScaffoldTemplates::API_ROUTES_TEMPLATE,

        // Route middleware for security schemes
        'security_schemes_middlewares' => [
            'AuthToken' => 'jwt.auth',
        ],

        // Result file location. File will be overwritten
        'result_file_name' => 'routes/api.php',

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
            ],
            'PUT' => [
                '/{{resourceName}}/{id}' => [
                    'action' => 'update',
                    'name' => '{{resourceName}}.update',
                ],
                '/auth' => [
                    'controller' => 'AuthApiController',
                    'action' => 'refreshToken',
                    'name' => 'authToken.refresh',
                ],
                '/auth/password/reset' => [
                    'controller' => 'ResetPasswordApiController',
                    'action' => 'reset',
                    'name' => 'password.reset',
                ],
            ],
            'DELETE' => [
                '/{{resourceName}}/{id}' => [
                    'action' => 'destroy',
                    'name' => '{{resourceName}}.destroy',
                ],
                '/auth' => [
                    'controller' => 'AuthApiController',
                    'action' => 'logout',
                    'name' => 'auth.logout',
                ],
            ],
        ],
    ],
];
