<?php

/*
 * 数据库配置文件
 */

return [
    /**
     * 默认使用连接名
     */
    'default' => 'mysql',
    /**
     * 连接信息配置，可支持的数据库类型由三方模块决定
     */
    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => workerEnv('DB_DATABASE', 'sqlite'),
            'prefix' => '',
            'foreign_key_constraints' => workerEnv('DB_FOREIGN_KEYS', true),
        ],
        'mysql' => [
            'driver' => 'mysql',
            'host' => workerEnv('DB_HOST', '127.0.0.1'),
            'port' => workerEnv('DB_PORT', '3306'),
            'database' => workerEnv('DB_DATABASE', 'forge'),
            'username' => workerEnv('DB_USERNAME', 'forge'),
            'password' => workerEnv('DB_PASSWORD', ''),
            'unix_socket' => workerEnv('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => 'by_',
            'prefix_indexes' => true,
            'strict' => false,
            'engine' => null,
            'options' => array_filter([
                PDO::MYSQL_ATTR_SSL_CA => workerEnv('MYSQL_ATTR_SSL_CA'),
            ]),
        ],
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => workerEnv('DB_HOST', '127.0.0.1'),
            'port' => workerEnv('DB_PORT', '5432'),
            'database' => workerEnv('DB_DATABASE', 'forge'),
            'username' => workerEnv('DB_USERNAME', 'forge'),
            'password' => workerEnv('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => workerEnv('DB_HOST', 'localhost'),
            'port' => workerEnv('DB_PORT', '1433'),
            'database' => workerEnv('DB_DATABASE', 'forge'),
            'username' => workerEnv('DB_USERNAME', 'forge'),
            'password' => workerEnv('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],
    ]
];
