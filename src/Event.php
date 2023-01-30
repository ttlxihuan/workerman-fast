<?php

/*
 * 服务事件处理类
 */

namespace WorkermanFast;

use App\Controllers\Controller;
use \GatewayWorker\Lib\Gateway;
use GatewayWorker\BusinessWorker;

class Event {

    /**
     * @var Annotation 控制器集
     */
    protected static $controllers;

    /**
     * @var BusinessWorker 业务处理服务实例
     */
    public static $businessWorker;

    /**
     * 初始化处理
     */
    public static function init() {
        // 控制器加载
        static::$controllers = new Annotation(Controller::class, '\\App\\Controllers', APP_PATH . '/Controllers');
    }

    /**
     * 当子进程启动后触发，只有一次
     * 每个子进程ID值不一样，可用来区分做不同的任务
     * 
     * @param BusinessWorker $businessWorker 子进程实例
     */
    public static function onWorkerStart(BusinessWorker $businessWorker) {
        date_default_timezone_set('PRC');
        Annotations\Timer::$id = $businessWorker->id;
        static::$businessWorker = $businessWorker;
        static::$controllers->callIndex('bind-call', 'start', $businessWorker->id);
        // 全局定时器启动
        $timers = new Annotation(\App\Timers\Timer::class, '\\App\\Timers', APP_PATH . '/Timers');
        $timers->callIndex('timer', "id:{$businessWorker->id}");
    }

    /**
     * 当子进程退出后触发，只有一次
     * 每个子进程ID值不一样，可用来区分做不同的任务
     * 
     * @param BusinessWorker $businessWorker 子进程实例
     */
    public static function onWorkerStop(BusinessWorker $businessWorker) {
        static::$controllers->callIndex('bind-call', 'stop', $businessWorker->id);
    }

    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id) {
        static::$controllers->callIndex('bind-call', 'connect', $client_id);
    }

    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $message 具体消息
     */
    public static function onMessage($client_id, $message) {
        $result = static::$controllers->call(Controller::class, $client_id, $message);
        Gateway::sendToCurrentClient($result);
    }

    /**
     * 当用户断开连接时触发
     * @param int $client_id 连接id
     */
    public static function onClose($client_id) {
        static::$controllers->callIndex('bind-call', 'close', $client_id);
    }

}
