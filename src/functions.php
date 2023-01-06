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
