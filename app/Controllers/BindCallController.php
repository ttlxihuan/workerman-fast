<?php

/*
 * 绑定功能处理，这里不建议指定路由信息
 */

namespace App\Controllers;

use Exception;
use App\Message;
use GatewayWorker\BusinessWorker;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use WorkermanAnnotation\BusinessException;

/**
 * @Register(class='BindCall')
 */
class BindCallController extends Controller {

    /**
     * http请求非正常时调用，无返回则无响应信息给终端
     * 1、无匹配路由时
     * 2、异常报错时（正常能捕获的异常）
     * @param Request $request      终端请求数据体
     * @param Exception $error      异常报错时有此参数
     * @return mixed
     * 
     * @BindCall()
     */
    public function http(Request $request, Exception $error = null) {
        if ($error) {
            if ($error instanceof BusinessException) {
                return new Response(401, [], $error->getMessage());
            } else {
                return new Response(500, [], 'Internal Server Error');
            }
        } else {
            return new Response(404, [], 'Not Found');
        }
    }

    /**
     * websocket请求非正常时调用，无返回则无响应信息给终端
     * 1、无匹配路由时
     * 2、异常报错时（正常能捕获的异常）
     * @param array $message        终端请求数据包
     * @param Exception $error      异常报错时有此参数
     * @return mixed
     * 
     * @BindCall()
     */
    public function websocket(array $message, Exception $error = null) {
        if ($error) {
            if ($error instanceof BusinessException) {
                return Message::make($error->getMessage(), $error->getCodeValue());
            } else {
                BusinessWorker::log("[ERROR] " . $error->getMessage() . PHP_EOL . $error->getTraceAsString());
                return Message::fail('Internal Server Error');
            }
        } else {
            return Message::fail('Not Found');
        }
    }

    /**
     * 业务进程启动时调用
     * @param int $id               启动的业务进程序号
     * 
     * @BindCall()
     */
    public function start(int $id) {
        BusinessWorker::log("[START] worker-id: $id");
    }

    /**
     * 业务进程停止时调用
     * @param int $id               启动的业务进程序号
     * 
     * @BindCall()
     */
    public function stop(int $id) {
        BusinessWorker::log("[STOP] worker-id: $id");
    }

    /**
     * 有终端连接时调用（不是网关连接）
     * @param string $client_id     终端唯一编号
     * 
     * @BindCall()
     */
    public function connect(string $client_id) {
        
    }

    /**
     * 终端连接关闭时调用（不是网关连接）
     * @param string $client_id     终端唯一编号
     * 
     * @BindCall()
     */
    public function close(string $client_id) {
        
    }

}
