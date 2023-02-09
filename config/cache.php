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
     * 缓存存储配置
     */
    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'host' => workerEnv('REDIS_HOST', '127.0.0.1'),
            'port' => workerEnv('REDIS_PORT', '6379'),
            'password' => workerEnv('REDIS_PASSWORD', ''),
            'db' => workerEnv('REDIS_DB', 0),
            'prefix' => '',
        ]
    ],
];
