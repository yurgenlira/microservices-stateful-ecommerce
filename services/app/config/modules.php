<?php

declare(strict_types=1);

return [
    'cache' => [
        'ttl' => [
            'user' => (int) env('CACHE_TTL_USER', 300),
            'catalog' => (int) env('CACHE_TTL_CATALOG', 600),
            'ordering' => (int) env('CACHE_TTL_ORDERING', 60),
            'inventory' => (int) env('CACHE_TTL_INVENTORY', 120),
        ],
    ],
];
