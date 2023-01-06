<?php

/*
 * 初始化引导处理
 */

require_once __DIR__ . '/functions.php';

// 自动加载类
require_once __DIR__ . '/../vendor/autoload.php';

$env_name = env('APP_ENV');
if (!$env_name) {
    for ($key = 1; $key < $argc; $key++) {
        $option = $argv[$key];
        if (strpos($option, '--env=')) {
            list(, $env_name) = explode('=', $option, 2);
        } elseif ($option === '--env') {
            $env_name = $argv[++$key] ?? '';
        }
    }
}

\WorkermanFast\Environment::load($env_name ?: 'production');

\WorkermanFast\Config::load(__DIR__ . '/../config');
