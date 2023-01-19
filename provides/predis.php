<?php

/*
 * 此文件是 predis/predis 模块初始处理文件
 * 当安装 predis/predis 模块时自动或指定加载此文件
 */

use Predis\Client;
use WorkermanFast\Cache;
use WorkermanFast\Annotations\Cache as CacheAnnotation;

if (!class_exists(Client::class)) {
    return;
}
// 添加连接生成器
Cache::addMakeConnection('redis', function (array $options) {
    $client = new Predis\Client("tcp://{$options['host']}:{$options['port']}", [
        'prefix' => $options['prefix'],
    ]);
    if (!empty($options['password'])) {
        $client->auth($options['password']);
    }
    if (!empty($options['db'])) {
        $client->select($options['db']);
    }
    return $client;
});

// 添加注解缓存处理
CacheAnnotation::addHandle(function($key, $data, $timeout, $name) {
    return Cache::connection($name)->setex($key, $timeout, $data);
}, function($key, $name) {
    return Cache::connection($name)->get($key);
});

class_exists('\Cache') || class_alias(Cache::class, '\Cache');

return true;
