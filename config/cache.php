<?php

/*
 * 缓存配置文件
 */

return [
    /**
     * 使用缓存，多个库时会自动按权重进行分配使用
     * 权重无上限，权重越大使用概率越大
     */
    'use' => [
        'redis' => 100
    ],
    /**
     * 缓存存储配置，暂时只支持redis和memcached缓存配置
     */
    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', '6379'),
            'password' => env('REDIS_PASSWORD', ''),
            'db' => env('REDIS_DB', 0),
            'prefix' => '',
        ],
        'memcached' => [
            'driver' => 'memcached',
            'host' => env('MEMCACHED_HOST', '127.0.0.1'),
            'port' => env('MEMCACHED_PORT', '11211'),
            'username' => env('MEMCACHED_USERNAME', ''),
            'password' => env('MEMCACHED_PASSWORD', ''),
            'prefix' => '',
        ],
    ],
];
