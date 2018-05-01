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

        // Path parameters substitutions. List of path variables in swagger that should be substituted during
        // route implementation guessing.
        // If key of this array item is equals to swagger path parameter name the it will be substituted by provided
        // values. Value should be compatible with ApiRouteParameterObject class structure.
        // Available placeholders for 'type' parameter:
        // - {{resourceClass}} - will be replaced with guessed route resource class
        // When placeholder value is empty then substitution is not performed
        'path_parameters_substitutions' => [
            // Swagger path parameter 'id' should be renamed to 'model' with type hinting of resource class:
            'id' => [
                'name' => 'model',
                'type' => '{{resourceClass}}',
                'description' => 'Related resource model',
            ],
        ],
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
            // This configuration is helpful when you need to initialize your API controller with
            // resource class name that this controller handles, for example.
            [
                'description' => 'Resource class that is handled by this API controller',
                'name' => 'modelClass',
                'type' => '{{resourceClass}}',
                'value' => '{{resourceClass}}::class',
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

        // List of middlewares that should be applied for root routes group
        'root_group_middlewares' => [
            'bindings',
        ],

        // Result file location. File will be overwritten
        'result_file_name' => 'routes/api.php',

        // Which route generator should be used to generate single route definition
        'route_generator' => \Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\ApiRouteGenerator::class,
        // 'route_generator' => \Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\ApiRouteResourceRegistrarGenerator::class,
        // 'route_generator' => \Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\ApiRouteModelBindingResourceRegistrarGenerator::class,

        // Which routes generator should be used to generate block of routes definition.
        // Block of routes is not a $api->group(), but semantically united routes
        'block_generator' => \Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\ApiRoutesBlockGenerator::class,

        // Which route generator should be used to generate routes group
        'group_generator' => \Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\ApiRoutesGroupGenerator::class,
        // 'group_generator' => \Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\ApiRoutesGroupResourceRegistrarGenerator::class,

        // Well-known routes which controller, action and route names should not be guessed and used from config
        // Array with method type contains list of known routes definitions. Supported placeholders are:
        // - {{resourceName}} - suggested resource name in plural form, like 'users' or 'roles' that will be detected in urls
        // Each known route should be compatible with KnownApiRouteObject class structure. If some attributes are empty then
        // they will be guessed
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
];
