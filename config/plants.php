<?php

return [
    'api_url' => env('PLANTS_API_URL', 'http://34.136.112.77/api/plantas-disponibles'),
    'timeout' => (int) env('PLANTS_API_TIMEOUT', 4),
    'cache_ttl' => (int) env('PLANTS_CACHE_TTL', 300), // 5 minutos en segundos
    'min_watches_for_gift' => 2,
];
