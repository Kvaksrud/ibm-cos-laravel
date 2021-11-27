<?php

return [

    'manager' => [
        'auth' => [
            env('IBM_COS_USERNAME', null),
            env('IBM_COS_PASSWORD', null)
        ],
        // Manager - Base URI - The COS Manager management interface URL with SSL
        'base_uri' => env('IBM_COS_MANAGER_BASE_URI','https://cos-manager.local')
    ],
    'service' => [
        'auth' => [
            env('IBM_COS_SERVICE_API_USERNAME', env('IBM_COS_USERNAME', null)), // Fallback to IBM_COS_USERNAME
            env('IBM_COS_SERVICE_API_PASSWORD', env('IBM_COS_PASSWORD', null))  // Fallback to IBM_COS_USERNAME
        ],
        // Service - Base URI - The COS Accessor Service API. Default port is 8338 with SSL
        'base_uri' => env('IBM_COS_SERVICE_BASE_URI','https://cos-manager.local:8338')
    ]

];
