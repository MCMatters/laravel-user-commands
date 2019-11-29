<?php

declare(strict_types = 1);

return [
    'models'   => [
        'user' => App\Models\User::class,
        'role' => App\Models\Role::class,
    ],
    'columns'  => [
        'id'       => 'id',
        'email'    => 'email',
        'name'     => 'name',
        'password' => 'password',
    ],

    // Settings for "user:update-password" command.
    'password' => [
        'need_hash' => false,
    ],

    // Settings for "user:sanitize" command.
    'sanitize' => [
        'scopes' => [],
    ],

    // Settings for "user:assign-role" command.
    'update_password' => [
        'role_identifiers' => [
            'id',
            'name',
        ],
        'attach_method' => 'attachRole'
    ],
];
