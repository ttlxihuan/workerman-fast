<?php

/*
 * 缓存处理
 */

namespace WorkermanFast;

class Cache {

    /**
     * @var array 连接生成处理器，内部全部是匿名函数
     */
    protected static $makes = [];

    /**
     * @var array 已经生成的连接
     */
    protected static $connections = [];

    /**
     * 添加连接生成器
     * @param string $driver
     * @param \Closure $callback
     */
    public static function addMakeConnection(string $driver, \Closure $callback) {
        
    }

    public static function connection($name = null) {
        
    }

    public static function __callStatic($name, $arguments) {
        
    }

}
