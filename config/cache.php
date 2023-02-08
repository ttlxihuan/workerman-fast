<?php

/*
 * 缓存配置文件
 */

return [
    /**
     * 默认使用连接名
     */
    'default' => 'redis',
    /**
     * 缓存存储配置，暂时只支持redis和memcached缓存配置
     */
    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'host' => workerEnv('REDIS_HOST', '127.0.0.1'),
            'port' => workerEnv('REDIS_PORT', '6379'),
            'password' => workerEnv('REDIS_PASSWORD', ''),
            'db' => workerEnv('REDIS_DB', 0),
            'prefix' => '',
        ],
        'memcached' => [
            'driver' => 'memcached',
            'host' => workerEnv('MEMCACHED_HOST', '127.0.0.1'),
            'port' => workerEnv('MEMCACHED_PORT', '11211'),
            'username' => workerEnv('MEMCACHED_USERNAME', ''),
            'password' => workerEnv('MEMCACHED_PASSWORD', ''),
            'prefix' => '',
        ],
    ],
];
