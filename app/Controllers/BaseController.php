<?php

/*
 * 控制器基础入口
 */

namespace App\Controllers;

/**
 * 
 * http路由注解
 * @Register(class='HttpRouter')
 * @Register(class='HttpMethod')
 * 
 * websocket路由注解
 * @Register(class='WebsocketRouter')
 * @Register(class='WebsocketMethod')
 * 
 * 使用路由
 * @HttpRouter()
 * @WebsocketRouter()
 * 
 * @SessionCache()
 */
abstract class BaseController extends Controller {
    
}
