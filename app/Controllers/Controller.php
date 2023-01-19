<?php

/*
 * 控制器基类，所有控制器类应该继承此类
 */

namespace App\Controllers;

/**
 * HTTP路由注解
 * @Register(class="WorkermanFast\Annotations\HttpRouter")
 * @Register(class="WorkermanFast\Annotations\HttpMethod")
 * 
 * websocket路由注解
 * @Register(class="WorkermanFast\Annotations\WebsocketRouter")
 * @Register(class="WorkermanFast\Annotations\WebsocketMethod")
 * 
 * 辅助处理
 * @Register(class="WorkermanFast\Annotations\UseWmiddleware")
 * @Register(class="WorkermanFast\Annotations\Validator")
 * 
 * 使用路由
 * @HttpRouter()
 * @WebsocketRouter()
 */
abstract class Controller {
    
}
