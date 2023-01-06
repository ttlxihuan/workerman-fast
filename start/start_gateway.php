<?php

/**
 * 网关收发服务启动处理
 */
use App\EnvConfig;
use \Workerman\Worker;
use \Workerman\WebServer;
use \GatewayWorker\Gateway;
use \GatewayWorker\BusinessWorker;
use \Workerman\Autoloader;

require_once __DIR__ . '/../src/bootstrap.php';

// gateway 进程，这里使用Text协议，可以用telnet测试
$gateway = new Gateway(config('server.gateway.listen'));
// gateway名称，status方便查看
$gateway->name = config('server.gateway.name');
// gateway进程数
$gateway->count = defined('PROCESS_NUM') ? PROCESS_NUM : 1;
// 本机ip，分布式部署时使用内网ip
$gateway->lanIp = config('server.gateway.host');
// 内部与业务处理通讯起始端口，假如$gateway->count=4，起始端口为4000
// 则一般会使用4000 4001 4002 4003 4个端口作为内部通讯端口 
$gateway->startPort = config('server.gateway.port');
// 服务注册地址
$gateway->registerAddress = config('server.register.addr');

// 与用户连接进行心跳处理，保持连接有效，部分协议长时间无通信会自动关闭连接
// 心跳间隔，终端30秒内未心跳强制关闭
$gateway->pingInterval = config('server.gateway.ping.interval');
// 超时未响应心跳强制关闭连接
$gateway->pingNotResponseLimit = config('server.gateway.ping.not_response');
// 心跳数据
$gateway->pingData = config('server.gateway.ping.send') ? config('server.gateway.ping.data') : '';

// 如果不是在根目录启动，则运行runAll方法
if (!defined('GLOBAL_START')) {
    Worker::runAll();
}

