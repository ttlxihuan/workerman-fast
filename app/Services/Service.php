<?php

/*
 * 服务处理基类，所有服务处理类应该继承此类
 * 
 * 推荐使用注解
  @Cache(timeout=int, name=string)
 * 使用位置： function、class
  缓存函数返回值专用注解，此注解会截取函数返回值并进行缓存，下次调用时在缓存有效期内直接返回缓存值而不需要调用函数。
 * - timeout   指定缓存保存时长（秒），默认600秒。
 * - name      指定缓存处理名，用来选择不同的缓存，不指定则为配置默认连接。
 * - empty     是否缓存空值（以empty语句结果为准），默认不缓存空值。

  @Transaction(name=string)
 * 使用位置： function、class
  事务注解，可以函数调用时自动开启事务，当有报错时事务回滚否则事务提交。
 * - name      指定事务名，用来选择不同的数据库，不指定则为配置默认连接。
 * 
 */

namespace App\Services;

use WorkermanAnnotation\AnnotationHandle;
use WorkermanAnnotation\BusinessException;

/**
 * @Register(class='Cache')
 * @Register(class='Transaction')
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
            self::$annotation = new AnnotationHandle(__CLASS__, __NAMESPACE__, __DIR__);
        }
        $key = static::class . '::' . $method;
        if (self::$annotation->hasCall($key)) {
            return self::$annotation->call($key, ...$params);
        }
        throw new BusinessException("未知服务处理调用：$key");
    }

}
