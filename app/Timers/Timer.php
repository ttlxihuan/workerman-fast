<?php

/*
 * 定时器处理基类，所有定时器处理类应该继承此类
 * 
 * 推荐使用注解
  @Timer(id=int, interval=int, persistent=bool) 
 * 使用位置： function
  定时器注解，多进程时可以绑定指定进程号上运行，方便管理各定时器，如果只有一个进程运行时进程号无效。
 * - id        业务服务进程ID，<0时绑定在所有业务服务进程上，默认：0
 * - interval  定时调用间隔时长，默认：1
 * - persistent 是否循环定时器，默认：true

 */

namespace App\Timers;

/**
 * @Register(class='Timer')
 */
abstract class Timer {
    
}
