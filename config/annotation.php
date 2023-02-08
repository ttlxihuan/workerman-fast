<?php

/*
 * 注解基本配置
 * 注解是整个处理入口，通过注解数据进行整理打包再调用即可完成业务处理
 */

return [
    /**
     * 控制器注解配置
     */
    'controller' => [
        App\Controllers\Controller::class,
        '\\App\\Controllers',
        __DIR__ . '/../app/Controllers'
    ],
    /**
     * 中间件注解配置
     * 中间件能嵌入到所有注解调用器中
     */
    'middleware' => [
        App\Middlewares\Middleware::class,
        '\\App\\Middlewares',
        __DIR__ . '/../app/Middlewares'
    ],
    /**
     * 定时器注解配置
     * 定时器方便完成一起定时操作
     */
    'timer' => [
        App\Timers\Timer::class,
        '\\App\\Timers',
        __DIR__ . '/../app/Timers'
    ],
];
