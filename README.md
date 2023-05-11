# workerman-fast
基于GatewayWorker的便捷应用结构，通过注解进行绑定路由、定时器、控制器等，方便快速搭建GatewayWorker应用。支持HTTP协议。

导航
-----------------
* [安装](#installation)
* [配置](#configuration)
* [使用](#use)
* [目录](#directory)
* [注解](#Annotation)
* [扩展](#Extend)
* [注意项](#attention)

Installation
------------

### 使用composer安装
```shell
composer create-project ttlphp/workerman-fast your-appname dev-master
```

Configuration
-------------
* 配置分两块分别在 ./config 和 ./env 两个目录内。
* ./env是环境变量配置可用来区分不同的运行环境加载不同的配置文件。
* ./config是服务运行必需配置文件。
* 同一个注册服务内不建议运行不同的监听协议，比如：http与websocket不建议运行在同一个服务层内。

use
-------------
### windows系统下启动
运行 server.bat

### linux系统下启动
运行 bash server.sh

### PHP命令启动
运行 php server.php

### 注解
workerman-fast 全部是通过注解进行绑定处理应用调用，注解只在启动时同步加载并绑定，后续不产生额外开销（占用内存除外）。
注解分PHP8和内置两种，PHP8自带注解和PHP7模仿注解。所有注解在使用前必需进行注册。

#### PHP7模仿注解
PHP7及以下版本内核没有注解处理功能，但能提取类或函数的注释信息，通过解析注释信息里规则的注解语句来生成注解数据。
##### 注解语法：
  @name(key=value, ...)

##### 注解说明：
注解小括号必需有，否则不会认定为注解
标记名               | 说明
:-------------------|:----------
 @      |   注解标记符
 name   |   注解名，可按正常PHP命名来定义
 key    |   注解参数名，可选
 value  |   注解参数值，允许使用字符串、数值、布尔值

##### 注解示例：
```php
/**
 * 示例注解
 * @param string $file
 * @return bool
 * @Register(class='Cache') 注册Cache注解使用
 * @Cache(timeout=600) 此处为有效注解
 */
```

directory
-------------
app目录是应用处理部分，bin目录为启动相关文件。在app目录中，除Annotations目录其它均需要配置注解信息。
服务在启动后会先加载各注解信息，然后通过注解信息处理定时器和路由。

### app/Middlewares
中间件处理层，主要用来嵌入请求处理层。
所有中间件处理类需要继承类App\Middlewares\Middleware

### app/Controllers
控制器处理层，通过使用注解绑定路由，当请求信息匹配到指定路由时则会自动调用，所有控制器类均为单例模式。
所有服务处理类需要继承类App\Controllers\Controller

### app/Models
数据模型处理层，依赖三方模块，这里提供了illuminate/database和doctrine/orm处理基础部分（使用前需要安装），方便使用。
启动时系统会自动判断是否存在指定模块并进行类别名处理，所有缓存处理类需要继承类App\Models\Model

### app/Services
服务处理层，此层为数据服务处理，是独立层，提供了缓存和依赖处理注解功能。
所有服务处理类需要继承类App\Services\Service

### app/Timers
定时器处理层，通过使用注解绑定定时器处理，可以指定定时时长和处理进程号（多进程使用），可以使用定时器只运行在某个进行中。
所有定时器处理类需要继承类App\Timers\Timer

### config/
服务配置目录，需要配置服务启动相关信息和数据库及缓存连接信息

### env/
环境配置文件，多环境使用，不同环境在启动时需要进行指定，默认环境 local

### provides/
用来配置三方模块初始化加载，如果加载成功则返回true，其它任何返回值认定为失败
多个相同模块加载只加载一个有效模块，此功能主要用于兼容多个三方便捷模块，目前有数据库和缓存两大模块初始处理

Annotation
-------------
### 内置注解
内置注解主要用于完成基本服务运行，如果不能满足要求还可以自定义注解处理器。
** 注意：函数必需是非静态并且公有使用注解才会被解析生效 **

#### @Register(class=string)
内置固定注解处理器，注册要使用的注解，使用注解前必需进行注册，注册后的注解能向下延续（即子类中可以使用）。
* class  注解处理类名，内置注解不需要指定全类名（即不含命名空间）。

#### @DefineUse(function=bool, class=bool)
内置固定注解处理器，注解处理类专用注解，用来指定为注解处理类可使用位置
* function  在方法上使用，默认否
* class     在类上使用，默认否

#### @DefineParam(name=string, type=string, default=mixed)
内置固定注解处理器，注解处理类专用注解，用来指定注解处理类参数，多个参数需要使用多次此注解。
* name      参数名
* type      参数数据类型，可选值：bool、int、float、string、mixed。PHP8注解额外可使用：array、object
* default   参数默认值

#### @BindCall(name=string)
绑定调用注解，可以注册 bind-call.name 的索引调用，等待特殊调用。此注解主要用于服务路由不匹配或服务部分事件处理。
* name      绑定索引名，内置固定：websocket（路由找不到或处理报错）、http（路由找不到或处理报错）、start（服务启动）、stop（服务停止）、connect（客户连接）、close（客户断开）

#### @HttpRouter(path=string)
HTTP请求路由处理注册，指定后就可以在服务处理事件时调用路由，完成请求操作。如果是静态文件需要存放在 public/ 目录下。
* path      路由前缀，默认：/

#### @HttpMethod(type=string, name=string)
HTTP请求方法路由注册，指定后此方法就可以通过路由调用。
* type      请求类型，不指定为所有类型均可路由，多个使用逗号分开，可选：GET、POST、OPTIONS、HEAD、DELETE、PUT、PATCH
* name      路由后缀，不指定为方法名

#### @WebsocketRouter(path=string, route=string)
WebSocket请求路由处理注册，指定后就可以在服务处理事件时调用路由，完成请求操作。内置xml、json两种数据通信，会自动进行匹配，默认json。
* path      路由前缀，默认：空
* route     路由键名，从通信数据里提取，响应时会自动增加，默认：type

#### @WebsocketMethod(name=string)
WebSocket请求方法路由注册，指定后此方法就可以通过路由调用。
* name      路由后缀，不指定为方法名

#### @Middleware(name=string)
中间件注册，注册后可通过使用中间件注解进行绑定切入调用。
* name      中间件调用名

#### @UseWmiddleware(name=string)
使用中间件，指定后就可以绑定指定中间件处理器。
* name      中间件名

#### @Provide(action=string, name=string)
三方外部扩展包注解加载处理，使用外部扩展时可通过注解进行加载，同一类型扩展加载成功一个即停止加载其它相同扩展。
* action    扩展动作名，相同类型的扩展使用一样的名称
* name      扩展名，用来加载 /provides/name.php 文件的，此文件返回真就停止加载其它相同类型扩展文件

#### @Cache(timeout=int, name=string)
缓存函数返回值专用注解，此注解会截取函数返回值并进行缓存，下次调用时在缓存有效期内直接返回缓存值而不需要调用函数。
* timeout   指定缓存保存时长（秒），默认600秒。
* name      指定缓存处理名，用来选择不同的缓存，不指定则为配置默认连接。
* empty     是否缓存空值（以empty语句结果为准），默认不缓存空值。

#### @Transaction(name=string)
事务注解，可以函数调用时自动开启事务，当有报错时事务回滚否则事务提交。
* name      指定事务名，用来选择不同的数据库，不指定则为配置默认连接。

#### @Timer(id=int, interval=int, persistent=bool)
定时器注解，多进程时可以绑定指定进程号上运行，方便管理各定时器，如果只有一个进程运行时进程号无效。
* id        业务服务进程ID，<0时绑定在所有业务服务进程上，默认：0
* interval  定时调用间隔时长，默认：1
* persistent 是否循环定时器，默认：true
* basis     指定基准时间（H:i:s），用于按标准时间间隔定时处理
* worker    指定启动业务进程名，用于多业务进程名时划分处理

#### @Validator(name=string, value=mixed, rules=string, title=string)
验证参数注解，用来验证函数的第一个参数（必需是数组）。
* name      参数（数组）键名
* value     默认值
* rules     验证规则
* title     字段名，验证失败时提示用，不指定为 name

#### @Log(timeout=int)
调用超时日志记录
* timeout   调用处理超时时长（秒）

### 自定义注解
当内置注解不够用时可以自定义注解处理器。每个注解均有对应一个处理类，这个类必需继承接口 WorkermanAnnotation\Annotations\iAnnotation 。
通过DefineUse和DefineParam注解进行绑定参数和使用位置。

```php
// 示例
/**
 * @DefineUse(function=true)
 * @DefineParam(name="name", type="string", default="")
 */
class TextAnnotation implements \WorkermanAnnotation\Annotations\iAnnotation {

    /**
     * 注解处理数据生成
     * @param array $params
     * @param array $input
     * @return array
     */
    public function make(array $params, array $input): array {
        return [
            function($params, \Closure $next){ // 返回切入处理器
                // $params 是使用注解的函数参数
                return $next();
            },
            // 返回索引， 可以通过注解处理器索引调用注解函数
            'test' => 'test'
        ];
    }

}
```

Extend
-------------
workerman 中没有合适的数据库和缓存操作模块，一般应用均需要使用数据库或缓存，这里推荐几个相关模块。
使用可查看各模块的 README.md 文件。
#### 数据库模块
* composer require illuminate/database

#### 缓存模块
* composer require predis/predis

attention
-------------
使用HTTP协议等短连接时不能使用 GatewayWorker\Lib\Gateway 工具 和 $_SESSION 全局变量，短连接没有推送的概念。
在短连接时使用SESSION可通过助手函数 getRequest()->session(); 进行提取处理。

