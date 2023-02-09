<?php

/*
 * 控制器基类，所有控制器类应该继承此类
 * 
 * 推荐使用注解
  @HttpRouter(path=string)
 * 使用位置： class
  HTTP请求路由处理注册，指定后就可以在服务处理事件时调用路由，完成请求操作。如果是静态文件需要存放在 public/ 目录下。
 * - path      路由前缀，默认：/

  @HttpMethod(type=string, name=string)
 * 使用位置： function
  HTTP请求方法路由注册，指定后此方法就可以通过路由调用。
 * - type      请求类型，不指定为所有类型均可路由，多个使用逗号分开，可选：GET、POST、OPTIONS、HEAD、DELETE、PUT、PATCH
 * - name      路由后缀，不指定为方法名

  @WebsocketRouter(path=string, route=string)
 * 使用位置： class
  WebSocket请求路由处理注册，指定后就可以在服务处理事件时调用路由，完成请求操作。内置xml、json两种数据通信，会自动进行匹配，默认json。
 * - path      路由前缀，默认：空
 * - route     路由键名，从通信数据里提取，响应时会自动增加，默认：type

  @WebsocketMethod(name=string)
 * 使用位置： function
  WebSocket请求方法路由注册，指定后此方法就可以通过路由调用。
 * - name      路由后缀，不指定为方法名

  @UseWmiddleware(name=string)
 * 使用位置： function、class
  使用中间件，指定后就可以绑定指定中间件处理器。
 * - name      中间件名

  @Validator(name=string, value=mixed, rules=string, title=string)
 * 使用位置： function、class
  验证参数注解，用来验证函数的第一个参数（必需是数组）。
 * - name      参数（数组）键名
 * - value     默认值
 * - rules     验证规则
 * - title     字段名，验证失败时提示用，不指定为 name

  @Provide(action=string, name=string)
 * 使用位置： class
  三方外部扩展包注解加载处理，使用外部扩展时可通过注解进行加载，同一类型扩展加载成功一个即停止加载其它相同扩展。
 * - action    扩展动作名，相同类型的扩展使用一样的名称
 * - name      扩展名，用来加载 /provides/name.php 文件的，此文件返回真就停止加载其它相同类型扩展文件
 */

namespace App\Controllers;

/**
 * http路由注解
 * @-Register(class='HttpRouter')
 * @-Register(class='HttpMethod')
 * 
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
 * @-HttpRouter()
 * @WebsocketRouter()
 * 
 * 加载缓存三方包
 * @Provide(name="predis", action="cache")
 * 
 * 加载数据库模型三方包
 * @Provide(name="laravel-model", action="model")
 */
abstract class Controller {
    
}
