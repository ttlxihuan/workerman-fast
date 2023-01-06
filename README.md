# workerman-fast
基于GatewayWorker的便捷应用结构，通过注解进行绑定路由、定时器、控制器等，方便快速搭建GatewayWorker应用。

目录
-----------------
* [安装](#installation)
* [配置](#configuration)
* [使用](#use)
* [注意项](#attention)

Installation
------------

### 使用composer安装
```shell
composer require ttlphp/workerman-fast dev-master
```

Configuration
-------------
* 1、配置启动协议及地址
* 2、配置
* 3、配置

use
-------------
### 注解
workerman-fast 全部是通过注解进行绑定处理应用调用，注解只在启动时同步加载并绑定，后续不产生额外开销（占用内存除外）。
注解分PHP8和内置两种，PHP8自带注解功能，但在PHP7无法通过内核解析，所以暂时只提供内置注解，暂不支持PHP8注解使用，但支持在PHP8中运行。

#### 内置注解
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
 * @cache(timeout=600) 此处为有效注解
 */
```

##### 支持注解：



### 目录结构
app目录是应用处理部分，bin目录为启动相关文件。在app目录中，除Annotations目录其它均需要配置注解信息。
服务在启动后会先加载各注解信息，然后通过注解信息处理定时器和路由。

#### app/Caches
缓存处理层，依赖三方模块，这里提供了predis/predis和doctrine/cache处理基础部分（使用前需要安装），方便使用。
启动时系统会自动判断是否存在指定模块并进行类别名处理，所有缓存处理类需要继承类App\Cache

#### app/Controllers
控制器处理层，通过使用注解绑定路由，当请求信息匹配到指定路由时则会自动调用，所有控制器类均为单例模式。
所有服务处理类需要继承类App\Controller

#### app/Models
数据模型处理层，依赖三方模块，这里提供了illuminate/database和doctrine/orm处理基础部分（使用前需要安装），方便使用。
启动时系统会自动判断是否存在指定模块并进行类别名处理，所有缓存处理类需要继承类App\Model

#### app/Services
服务处理层，此层为数据服务处理，是独立层，提供了缓存和依赖处理注解功能。
所有服务处理类需要继承类App\Service

#### app/Timers
定时器处理层，通过使用注解绑定定时器处理，可以指定定时时长和处理进程号（多进程使用），可以使用定时器只运行在某个进行中。
所有定时器处理类需要继承类App\Timer

#### bin/
服务启动目录，包含windows和linux系统的启动命令文件
##### windows系统
* 启动服务 start.bat
* 停止服务 stop.bat
* 重启服务 restart.bat

##### linux系统
* 启动服务 start.sh
* 停止服务 stop.sb
* 重启服务 restart.sb

#### config/
服务配置目录，需要配置服务启动相关信息和数据库及缓存连接信息

#### env/
环境配置文件，多环境使用，不同环境在启动时需要进行指定，默认环境 local

#### src/
workerman-fast内部处理所有核心文件，主要有：注解处理、路由处理、配置处理、模型处理、缓存处理等

#### support/
用来配置三方模块初始化加载，如果加载成功则返回true，其它任何返回值认定为失败
多个相同模块加载只加载一个有效模块，此功能主要用于兼容多个三方便捷模块，目前有数据库和缓存两大模块初始处理

attention
-------------
workerman 中没有合适的数据库和缓存操作模块，一般应用均需要使用数据库或缓存，这里推荐几个相关模块。
使用可查看各模块的 README.md 文件。**特别说明：以下模块会生成很多文件，会额外增加项目开销**
#### 数据库模块
* composer require illuminate/database
* composer require doctrine/orm

#### 缓存模块
* composer require predis/predis
* composer require doctrine/cache
