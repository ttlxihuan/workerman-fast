<?php

/*
 * 服务配置文件
 * workerman分：注册中心、网关收发、业务处理 三个服务组成。
 * 
 * 【服务说明】
 * 注册中心：用来记录网关服务地址提供给业务服务进行连接通信，是一个内部服务（不建议使用外网地址）。
 *          注册中心服务不允许开放多子进程，高可用场景可运行2个及以上的注册中心服务用来应对。
 * 网关服务：是对外用户服务与对内业务处理的桥梁，将接收到的用户数据传发给业务服务处理，并由业务服务通知网关回复或推送给用户信息。
 *          允许并建议开启多子进程，用来增加并发处理能力。网关还提供：分组、标记、session处理等工作。
 *          网关服务会启动两个监听服务：对外用户服务（使用外网）、对内业务处理服务（使用内网）。
 *          对外用户服务监听地址所有子进程均相同，对内业务处理服务监听地址各子进程均不相同。
 * 业务服务：用来处理网关转发过来的用户信息，处理完后再通知给网关回复信息，由网关回复给用户。
 *          允许并建议开启多子进程，用来增加并发处理能力。每个子进程均会独立连接所有网关子进程。
 * 定时服务：用来处理注解定时器，将定时处理与业务处理剥离可减少相互影响。
 *          允许并建议开启多子进程，用来增加并发处理能力。每个子进程均会独立处理指派的定时任务。
 * 
 * 特别说明：只有网关接收用户接连，也就是只有网关提供对外不同协议选择，其它服务使用的协议均是内置固定的。
 * 
 * 【配置分布式】
 * 应该在启动参数上指定 --node=name  系统会自动加载环境配置文件 envname-nodename.env （环境配置文件名必需按这种格式配置）。
 * 系统通过 envname-nodename.env 环境文件进行区分加载不同的配置信息进行运行服务。
 * 分布式节点配置分三块：
 *  注册中心：最少运行一台以上，允许与其它服务运行在同一服务器中。
 *  网关服务：对外服务地址有几个就配置几个环境配置文件，启动指定节点名，允许与其它服务运行在同一服务器中。（对外服务协议不建议使用混合在同一注册中心，即使用HTTP就不要混合websocket）
 *  业务服务：业务配置无监听地址是通过连接注册中心获取网关信息的，即所有业务服务配置均一样即可。
 *  定时服务：定时服务无监听地址是完全独立与业务处理部分，通过注册中心地址进行网关数据转发。
 * 
 * 特别说明：多注册中心时需要配置 register_addresses ，将所有注册中心地址指定在内。单一注册中心里可不配置 register_addresses 。
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
        'active' => workerEnv('REGISTER_ACTIVE', false),
        /**
         * 服务注册地址，地址中不可指定协议，服务注册协议是内置固定的
         * 分布式时建议使用局域网地址，单机时使用本地地址
         */
        'addr' => workerEnv('REGISTER_ADDR', '127.0.0.1:18000'),
    ],
    /**
     * 网关配置
     * 文档：https://www.workerman.net/doc/gateway-worker/gateway.html
     */
    'gateway' => [
        /**
         * 分布式需要配置此项，可以保证只在指定节点中启动
         */
        'active' => workerEnv('GATEWAY_ACTIVE', false),
        /**
         * 网关进程名称，用来区分不同的节点
         */
        'name' => workerEnv('GATEWAY_NAME', 'workerman-gateway'),
        /**
         * 处理进程数，不指定 = CPU核数
         * windows系统下无效，永远为1
         */
        'count' => null,
        /**
         * 网关对外监听协议及地址，用来与用户进行通信
         * 支持的协议有：websocket、text、frame、自定义协议、tcp等
         */
        'listen' => workerEnv('GATEWAY_LISTEN', 'websocket://0.0.0.0:16000'),
        /**
         * 启用ssl加密处理。如果是http则会变为https，如果是ws则会变成wss
         * 证书最好是购买的，这样才能保证终端验证顺利通过
         * 此选项需要openssl扩展并且 Workerman >= 3.3.7
         */
        'context' => [
//            'ssl' => [
//                'local_cert' => 'server.pem', // 也可以是crt文件
//                'local_pk' => 'server.key',
//                'verify_peer' => false, // 是否验证证书
//                'allow_self_signed' => true, //如果是自签名证书需要开启此选项
//            ]
        ],
        /**
         * 网关与业务服务进行连接的地址配置，分布式时使用局域名，单机时使用本地地址
         */
        'host' => workerEnv('GATEWAY_HOST', '127.0.0.1'),
        /**
         * 网关与业务服务进行连接的监听端口号，此端口号会与网关进程数相关
         * 每个进程数是在此端口号上进行累加值，因此当进程数据有多个时监听端口号将有多个
         * 端口号需要保证累加后有空余和不被占用
         */
        'port' => workerEnv('GATEWAY_PORT', '19000'),
        /**
         * WebSocket协议连接网关层回调处理
         * 回调中不建议有资源等待操作（比如：写文件、网络请求等），以免影响网关性能
         */
        'onWebSocketConnect' => App\Controllers\BindCallController::class . '::onGatewayWebSocketConnect',
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
             * 当用户在 not_response * interval 时间内有发送信息给网关，则网关跳过发送本次心跳包
             * 一般不建议网关主动发送心跳包，建议让用户端主动发送心跳包，只要用户发送间隔时间小于网关心跳时长即可
             */
            'send' => 0,
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
        'active' => workerEnv('WORKER_ACTIVE', false),
        /**
         * 业务进程名称，用来区分不同的节点
         */
        'name' => workerEnv('WORKER_NAME', 'workerman-worker'),
        /**
         * 处理进程数，不指定 = CPU核数 * 6
         * windows系统下无效，永远为1
         */
        'count' => null
    ],
    /**
     * 定时器处理，剥离于业务处理可避免相互影响
     * 文档：https://www.workerman.net/doc/gateway-worker/timer.html
     */
    'timer' => [
        /**
         * 分布式需要配置此项，可以保证只在指定节点中启动
         */
        'active' => workerEnv('TIMER_ACTIVE', false),
        /**
         * 进程名称，用来区分不同的节点
         */
        'name' => workerEnv('TIMER_NAME', 'workerman-timer'),
        /**
         * 处理进程数
         * windows系统下无效，永远为1
         */
        'count' => 1
    ],
    /**
     * 服务运行时区
     */
    'timezone' => 'PRC',
];
