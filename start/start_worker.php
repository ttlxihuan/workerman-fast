<?php

/**
 * 业务处理服务启动文件
 */
use \Workerman\Worker;
use \GatewayWorker\BusinessWorker;

require_once __DIR__ . '/../src/bootstrap.php';

// 分布时此文件启动按需使用
if (!config('server.worker.active', true)) {
    return;
}

// bussinessWorker 进程
$worker = new BusinessWorker();
// worker名称
$worker->name = config('server.worker.name');
// bussinessWorker进程数量
$worker->count = defined('PROCESS_NUM') ? PROCESS_NUM : 1;
// 服务注册地址
$worker->registerAddress = config('server.register.addr');
// 事件处理
$worker->eventHandler = \WorkermanFast\Event::class;

// 如果不是在根目录启动，则运行runAll方法
if (!defined('GLOBAL_START')) {
    Worker::runAll();
}
