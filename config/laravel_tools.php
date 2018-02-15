<?php

return [
    'models' => [
        // Path where models located
        'path' => app_path('Models'),

        // Models namespace
        'namespace' => 'App\Models',

        // TODO implement and use
        // Suggest that model contain constants with attribute names (like const FIRST_NAME = 'first_name')
        'use_attribute_names_constants' => true,
    ],

    'form_requests' => [
        // Path where form requests located
        'path' => app_path('Http/Requests'),

        // Form requests namespace
        'namespace' => 'App\Http\Requests',

        //Form requests parent class FQN
        'parent' => \Illuminate\Foundation\Http\FormRequest::class,

        // Attributes that should be not taken into account
        'except' => [
            'id',
            'created_at',
            'updated_at',
            'deleted_at',
        ],
    ],
];
