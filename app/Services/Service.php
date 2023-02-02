<?php

/*
 * 服务处理基类，所有服务处理类应该继承此类
 */

namespace App\Services;

use WorkermanFast\Annotation;
use WorkermanFast\BusinessException;

/**
 * @Register(class='WorkermanFast\Annotations\Provide')
 * @Register(class='WorkermanFast\Annotations\Cache')
 * @Register(class='WorkermanFast\Annotations\Transaction')
 * 
 * 加载缓存三方包
 * @Provide(name="predis", action="cache")
 * @Provide(name="doctrine-cache", action="cache")
 * 
 * 加载数据库模型三方包
 * @Provide(name="laravel-model", action="model")
 * @Provide(name="doctrine-orm", action="model")
 */
abstract class Service {

    /**
     * @var Annotation 注解处理器
     */
    private static $annotation;

    /**
     * 动态调用函数，支持注解功能
     * @param string $method
     * @param mixed $params
     * @return mixed
     */
    public static function call($method, ...$params) {
        if (empty(self::$annotation)) {
            self::$annotation = new Annotation(__CLASS__, __NAMESPACE__, __DIR__);
        }
        $key = static::class . '::' . $method;
        if (self::$annotation->hasCall($key)) {
            return self::$annotation->call($key, ...$params);
        }
        throw new BusinessException("未知服务调用文件：$key");
    }

}
