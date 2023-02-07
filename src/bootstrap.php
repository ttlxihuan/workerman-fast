<?php

/*
 * 初始化引导处理
 */

require_once __DIR__ . '/functions.php';

// 自动加载类
require_once __DIR__ . '/../vendor/autoload.php';

defined('BASE_PATH') || define('BASE_PATH', realpath(__DIR__ . '/../'));
defined('APP_PATH') || define('APP_PATH', realpath(BASE_PATH . '/app'));

(function() {
    // 环境变量加载
    $env_name = env('APP_ENV') ?: consoleArgv('env', 'production');

    // 分布式处理，需要配置多个环境变量文件，以适应不同和节点启动
    $node = consoleArgv('node');
    if ($node) {
        $env_name .= "-$node";
    }

    \WorkermanFast\Environment::load($env_name ?: 'production');
})();

// 配置加载
\WorkermanFast\Config::load(BASE_PATH . '/config');

