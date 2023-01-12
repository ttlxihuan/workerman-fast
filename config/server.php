<?php

/*
 * 服务配置文件
 * workerman分：服务注册、网关收发、业务处理 三服务组成。
 * 
 * 服务注册：用来记录网关和业务服务连接地址，是一个内部服务（不建议使用外网地址），多节点或集群运行时建议启动一个，启动多个会导致不同服务注册所关联的业务处理数据分离
 * 网关收发：用来接收或回复用户信息，是对外提供服务的入口，网关只负责收发信息、分组、标记、session处理等工作。
 *          网关会通过服务注册获取业务服务连接信息并进行连接，当有用户请求信息过来时会转发给其中一个业务处理进行处理，并将处理结果信息返回给用户
 * 业务服务：用来处理网关转发过来的用户信息，处理完后再返回给网关，由网关回复给用户。业务服务是内部服务（不建议使用外网地址）
 * 
 * 特别说明：只有网关允许指定不同的连接协议，服务注册和业务处理服务均不可指定或修改连接协议，在这三个服务中已经固定了服务注册和业务处理连接协议，修改后将不可连接。
 * 
 * 如果需要配置分布式，则应该在启动参数上指定 --node=name  系统会自动加载环境配置文件 envname-nodename.env （环境配置文件名必需按这种格式配置）
 * 
 */

return [
    /**
     * 注册服务配置
     * 文档：https://www.workerman.net/doc/gateway-worker/register.html
     */
    'register' => [
        /**
         * 分布式需要配置此项，可以保证只在指定节点中启动
         */
        'active' => env('REGISTER_ACTIVE', false),
        /**
         * 服务注册地址，地址中不可指定协议，服务注册协议是内置固定的
         * 分布式时建议使用局域网地址，单机时使用本地地址
         */
        'addr' => env('REGISTER_ADDR', '127.0.0.1:18000'),
    ],
    /**
     * 网关配置
     * 文档：https://www.workerman.net/doc/gateway-worker/gateway.html
     */
    'gateway' => [
        /**
         * 分布式需要配置此项，可以保证只在指定节点中启动
         */
        'active' => env('GATEWAY_ACTIVE', false),
        /**
         * 网关进程名称，用来区分不同的节点
         */
        'name' => env('GATEWAY_NAME', 'workerman-gateway'),
        /**
         * 网关对外监听协议及地址，用来与用户进行通信
         * 支持的协议有：websocket、text、frame、自定义协议、tcp等
         */
        'listen' => env('GATEWAY_LISTEN', 'websocket://0.0.0.0:16000'),
        /**
         * 网关与业务服务进行连接的地址配置，分布式时使用局域名，单机时使用本地地址
         */
        'host' => env('GATEWAY_HOST', '127.0.0.1'),
        /**
         * 网关与业务服务进行连接的监听端口号，此端口号会与网关进程数相关
         * 每个进程数是在此端口号上进行累加值，因此当进程数据有多个时监听端口号将有多个
         * 端口号需要保证累加后有空余和不被占用
         */
        'port' => env('GATEWAY_PORT', '19000'),
        /**
         * 网关与用户的连接进行心跳处理
         * 心跳包用来保持连接状态，分网关给用户发和用户给网关发两种模式
         * 心跳包处理是通过循环所有连接进行判断处理的，连接数越多越耗性能，间隔时间不建议太短
         */
        'ping' => [
            /**
             * 心跳间隔时长（秒），间隔时长 <= 0 时不处理心跳包，一般建议在60秒以内
             * 心跳包越小越好，心跳包是无效数据，发送仅仅是为了保持连接状态
             */
            'interval' => 50,
            /**
             * 用户端无响应次数（每次是一个心跳间隔时长）
             * 此选项要求用户必需在 not_response * interval 时间内发送信息给网关（任何信息）
             */
            'not_response' => 1,
            /**
             * 是否开启网关向用户端主动定时发送的心跳数据包
             * 当用户在 not_response * interval 时间内有发送信息给网关，则网关路过发送本次心跳包
             * 一般不建议网关主动发送心跳包，建议让用户端主动发送心跳包，只要用户发送间隔时间小于网关心跳时长即可
             */
            'send' => 1,
            /**
             * 心跳数据包
             * 当开启网关发送心跳包时以此数据为心跳
             * 当用户端发送此数据时则判定为心跳包并路过业务处理
             */
            'data' => 'ping',
        ]
    ],
    /**
     * 业务处理配置
     * 文档：https://www.workerman.net/doc/gateway-worker/business-worker.html
     */
    'worker' => [
        /**
         * 分布式需要配置此项，可以保证只在指定节点中启动
         */
        'active' => env('WORKER_ACTIVE', false),
        /**
         * 业务进程名称，用来区分不同的节点
         */
        'name' => env('WORKER_NAME', 'workerman-worker'),
    ],
    /**
     * 服务运行时区
     */
    'timezone' => 'PRC',
];
