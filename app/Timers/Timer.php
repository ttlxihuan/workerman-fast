<?php

/*
 * 定时器处理基类，所有定时器处理类应该继承此类
 * 
 * 推荐使用注解
  @Timer(id=int, interval=int, persistent=bool)
 * 使用位置： function
  定时器注解，多进程时可以绑定指定进程号上运行，方便管理各定时器，如果只有一个进程运行时进程号无效。
 * - id        定时器进程ID，<0时绑定在所有定时器进程上，默认：0
 * - interval  定时调用间隔时长，默认：1
 * - persistent 是否循环定时器，默认：true
 * - basis     指定基准时间（H:i:s），用于按标准时间间隔定时处理
 * - worker    指定启动进程名，用于多进程名定时器服务划分处理

 */

namespace App\Timers;

/**
 * @Register(class='Timer')
 * @Register(class='Provide')
 * 
 * 加载缓存三方包
 * @Provide(name="predis", action="cache")
 * 
 * 加载数据库模型三方包
 * @Provide(name="laravel-model", action="model")
 */
abstract class Timer {
    
}
