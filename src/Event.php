<?php

/*
 * 服务事件处理类
 */

namespace WorkermanFast;

use \GatewayWorker\Lib\Gateway;
use GatewayWorker\BusinessWorker;
use Workerman\Lib\Timer as TimerWorker;

class Event {

    /**
     * @var ArrayAccess 控制器集
     */
    protected static $controllers;

    /**
     * @var ArrayAccess 中间件集
     */
    protected static $middlewares;

    /**
     * @var string 心跳数据包
     */
    protected static $ping;

    /**
     * 当子进程启动后触发，只有一次
     * 每个子进程ID值不一样，可用来区分做不同的任务
     * 
     * @param BusinessWorker $businessWorker 子进程实例
     */
    public static function onWorkerStart(BusinessWorker $businessWorker) {
        date_default_timezone_set('PRC');
        Log::info('初始化业务进程ID:' . $businessWorker->id);
        static::$ping = config('server.gateway.ping.data');
        // 控制器加载
        static::$controllers = new Annotation(\App\Controllers\Controller::class, '\\App\\Controllers', __DIR__ . '/../app/Controllers');
        // 中间件加载
        static::$middlewares = new Annotation(\App\Middlewares\Middleware::class, '\\App\\Middlewares', __DIR__ . '/../app/Middlewares');
        static::callEventMiddleware('start', ['id' => $businessWorker->id]);
        // 全局定时器启动
        $timers = new Annotation(\App\Timers\Timer::class, '\\App\\Timers', __DIR__ . '/../app/Timers');
        foreach ($timers->get('timer', $businessWorker->id) as $timer) {
            TimerWorker::add($timer['interval'], $timer['@call'], [], $timer['persistent'] ?? true);
        }
    }

    /**
     * 当子进程退出后触发，只有一次
     * 每个子进程ID值不一样，可用来区分做不同的任务
     * 
     * @param BusinessWorker $businessWorker 子进程实例
     */
    public static function onWorkerStop(BusinessWorker $businessWorker) {
        Log::info('结束业务进程ID:' . $businessWorker->id);
        static::callEventMiddleware('stop', ['id' => $businessWorker->id]);
    }

    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id) {
        Log::info('终端连接:' . $client_id);
        static::callEventMiddleware('connect', compact('client_id'));
    }

    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $message 具体消息
     */
    public static function onMessage($client_id, $message) {
        // 心跳包跳过
        if ($message === static::$ping) {
            return;
        }
        try {
            Log::receive($message);
            $params = Message::decode($message);
            if (!is_array($params) || empty($params['type']) || $params['type'] == 'empty' || !static::callEventMiddleware('message', compact('client_id', 'params'))) {
                return;
            }
            foreach (static::$controllers->get('request', $params['type']) as $request) {
                $attaches = $request['@attaches'];
                if (isset($attaches['useWmiddleware'])) {
                    foreach ($attaches['useWmiddleware'] as $wmiddleware) {
                        if (isset($wmiddleware['action']) && !static::callEventMiddleware($wmiddleware['action'], compact('client_id', 'params'))) {
                            return;
                        }
                    }
                }
                if (isset($attaches['validator'])) {
                    Validator::adopt($params, $attaches['validator']);
                }
                $msg = call_user_func($request['@call'], $client_id, $params);
                if ($msg) {
                    if (is_array($msg)) {
                        $msg['type'] = $params['type'];
                        $msg = Message::success($msg);
                    }
                    $msg = Message::encode($msg);
                    Log::send($msg);
                    Gateway::sendToCurrentClient($msg);
                }
            }
        } catch (\Exception $err) {
            $exception = BusinessException::convert($err, $params['type'] ?? 'exitgame', '服务器繁忙');
            $exception->sendToCurrentClient();
        }
    }

    /**
     * 当用户断开连接时触发
     * @param int $client_id 连接id
     */
    public static function onClose($client_id) {
        Log::info('关闭终端:' . $client_id);
        static::callEventMiddleware('close', compact('client_id'));
    }

    /**
     * 调用事件中间处理层
     * @param string $name
     * @param array $params
     * @return boolean
     */
    protected static function callEventMiddleware(string $name, array $params = []) {
        foreach (static::$middlewares->get('wmiddleware', $name) as $middleware) {
            try {
                if (false === call_user_func($middleware['@call'], $name, $params)) {
                    return false;
                }
            } catch (BusinessException $err) {
                if ($name != 'close') {
                    if (isset($params['params']['type'])) {
                        throw $err;
                    }
                    $err->sendToCurrentClient();
                }
                return false;
            } catch (\Exception $err) {
                Log::error($err);
                return false;
            }
        }
        return true;
    }

}
