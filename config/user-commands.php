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
    'password' => [
        'need_hash' => false,
    ],
    'sanitize' => [
        'scopes' => [],
    ],
    'update_password' => [
        'role_identifiers' => [
            'id',
            'name',
        ],
        'attach_method' => 'attachRole'
    ],
];
