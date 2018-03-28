<?php

return [
    'models' => [
        // Path where models located
        'path' => app_path('Models'),

        // Models namespace
        'namespace' => 'App\Models',

        // Suggest that model contain constants with attribute names (like const FIRST_NAME = 'first_name')
        'suggest_attribute_names_constants' => true,
    ],

    'form_requests' => [
        // Path where form requests located
        'path' => app_path('Http/Requests'),

        // Form requests namespace
        'namespace' => 'App\Http\Requests',

        // Form requests parent class FQN
        'parent' => \Illuminate\Foundation\Http\FormRequest::class,

        // Attributes that should not be taken into account
        'except' => [
            'id',
            'created_at',
            'updated_at',
            'deleted_at',
        ],

        // Form request class template. If template name is just a string than template from package will be taken.
        // If path passed then file by this path will be taken
        'template_file_name' => \Saritasa\LaravelTools\Enums\ScaffoldTemplates::FORM_REQUEST_TEMPLATE,
    ],

    'rules' => [
        // Validation rules dictionary
        'dictionary' => \Saritasa\LaravelTools\Rules\StringValidationRulesDictionary::class,
        // 'dictionary' => \Saritasa\LaravelTools\Rules\FluentValidationRulesDictionary::class
    ],

    'dto' => [
        // Path where DTOs are located
        'path' => app_path('Models/Dto'),

        // DTO classes namespace
        'namespace' => 'App\Models\Dto',

        // DTO parent class FQN
        'parent' => \Saritasa\Dto::class,

        // Immutable DTO parent class FQN in case you need immutable DTO
        'immutable_parent' => \Saritasa\Dto::class,

        // Strict-typed DTO parent class FQN in case you need DTO with strong attribute types
        'strict_type_parent' => \Saritasa\Dto::class,

        // Strict-typed DTO parent class FQN in case you need immutable DTO with strong attribute types
        'immutable_strict_type_parent' => \Saritasa\Dto::class,

        // DTO class template. If template name is just a string than template from package will be taken.
        // If path passed then file by this path will be taken
        'template_file_name' => \Saritasa\LaravelTools\Enums\ScaffoldTemplates::DTO_TEMPLATE,

        // Attributes that should not be taken into account
        'except' => [
            'deleted_at',
        ],
    ],
];
