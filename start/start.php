<?php

/**
 * 运行workerman服务入口文件
 * php start.php start
 */
ini_set('display_errors', 'on');

use Workerman\Worker;

if (strpos(strtolower(PHP_OS), 'win') === 0) {
    exit("此启动文件不可以windows系统下运行，windows系统请使用server.bat脚本启动。");
}

// 检查扩展
if (!extension_loaded('pcntl')) {
    exit("请安装 pcntl 扩展再启动服务。 安装说明地址： http://doc3.workerman.net/appendices/install-extension.html\n");
}

if (!extension_loaded('posix')) {
    exit("请安装 posix 扩展再启动服务。 安装说明地址： http://doc3.workerman.net/appendices/install-extension.html\n");
}

// 获取进程数
define('PROCESS_NUM', max(2, substr_count(file_get_contents("/proc/cpuinfo"), "processor")));
// 标记是全局启动
define('GLOBAL_START', 1);

require_once __DIR__ . '/../src/bootstrap.php';

// 加载所有 start_*.php 启动文件，以便启动所有服务
foreach (glob(__DIR__ . '/start_*.php') as $start_file) {
    require_once $start_file;
}
// 运行所有服务
Worker::runAll();
