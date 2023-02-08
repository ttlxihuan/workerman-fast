<?php

/*
 * 控制器基类，所有控制器类应该继承此类
 */

namespace App\Controllers;

/**
 * HTTP路由注解
 * @Register(class='HttpRouter')
 * @Register(class='HttpMethod')
 * 
 * websocket路由注解
 * @Register(class='WebsocketRouter')
 * @Register(class='WebsocketMethod')
 * 
 * 辅助处理
 * @Register(class='UseWmiddleware')
 * @Register(class='Validator')
 * 
 * 使用路由
 * @HttpRouter()
 * @WebsocketRouter()
 */
abstract class Controller {
    
}
