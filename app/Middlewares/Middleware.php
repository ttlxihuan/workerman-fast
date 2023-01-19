<?php

/*
 * 中间件处理，主要分两块：控制器、服务事件
 * 控制器中使用 @UseWmiddleware(name) 进行指派
 * 服务事件固定调用
 *      start       业务进程启动时调用
 *      stop        业务进行终止时调用
 *      connect     用户新连接时调用
 *      message     用户回复消息时调用（主要是信息解码）
 *      send        发送用户消息时调用（主要是信息编码）
 *      close       用户连接关闭时调用
 */

namespace App\Middlewares;

/**
 * @Register(class="WorkermanFast\Annotations\Middleware")
 */
abstract class Middleware {
    
}
