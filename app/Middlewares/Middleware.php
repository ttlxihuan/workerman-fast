<?php

/*
 * 中间件处理，主要分两块：控制器、服务事件
 * 
 * 推荐使用注解
  @Middleware(name=string)
 * 使用位置： function
  中间件注册，注册后可通过使用中间件注解进行绑定切入调用。
 * - name      中间件调用名
 */

namespace App\Middlewares;

/**
 * @Register(class='Middleware')
 */
abstract class Middleware {
    
}
