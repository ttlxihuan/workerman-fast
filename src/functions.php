<?php

/*
 * 助手函数库
 */

/**
 * 获取环境数据
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function env(string $key, $default = null) {
    return \WorkermanFast\Environment::get($key, $default);
}

/**
 * 获取配置数据
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function config(string $key = null, $default = null) {
    return WorkermanFast\Config::get($key, $default);
}

/**
 * 获取控制台参数
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function consoleArgv(string $key, $default = null) {
    global $argc, $argv;
    for ($key = 1; $key < $argc; $key++) {
        $option = $argv[$key];
        if (strpos($option, "--$key=")) {
            list(, $value) = explode('=', $option, 2);
        } elseif ($option === "--$key") {
            $value = $argv[++$key] ?? '';
            // 如果值是参数结构则不认为是指定值
            if (strpos($value, '-') === 0) {
                unset($value);
                $key--;
            }
        }
    }
    return $value ?? $default;
}
