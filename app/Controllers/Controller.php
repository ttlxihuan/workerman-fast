<?php

/*
 * 控制器基类，所有控制器类应该继承此类
 */

namespace App\Controllers;

/**
 * websocket路由注解
 * @Register(class='WebsocketRouter')
 * @Register(class='WebsocketMethod')
 * 
 * 辅助处理
 * @Register(class='UseWmiddleware')
 * @Register(class='Validator')
 * 
 * @Register(class='Provide')
 * 
 * 使用路由
 * @WebsocketRouter()
 * 
 * 加载缓存三方包
 * @Provide(name="predis", action="cache")
 * @Provide(name="doctrine-cache", action="cache")
 * 
 * 加载数据库模型三方包
 * @Provide(name="laravel-model", action="model")
 * @Provide(name="doctrine-orm", action="model")
 */
abstract class Controller {
    
}
