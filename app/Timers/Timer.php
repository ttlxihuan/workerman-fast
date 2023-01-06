<?php

/*
 * 定时器处理基类，所有定时器处理类应该继承此类
 */

namespace App\Timers;

/**
 * @register(name="timer", key="id")
 * @timer(id=0, interval=1, persistent=true)
 */
abstract class Timer {
    
}
