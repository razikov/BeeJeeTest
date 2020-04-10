<?php

return [
    'roles' => [
        'administrator' => [],
        'user' => ['administrator'],
    ],
    'permissions' => [
        'user' => [
            'job.update',
        ],
        'administrator' => [
            'job.updateForm',
        ],
    ],
];
