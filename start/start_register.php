<?php

/**
 * 服务注册服务启动处理
 */
use \Workerman\Worker;
use \GatewayWorker\Register;

require_once __DIR__ . '/../src/bootstrap.php';

\WorkermanFast\Log::info('运行环境：' . env('APP_ENV'));

// 分布时不需要启动该文件
// register 必须是text协议
$register = new Register('text://' . config('server.register.addr'));

// 如果不是在根目录启动，则运行runAll方法
if (!defined('GLOBAL_START')) {
    Worker::runAll();
}

