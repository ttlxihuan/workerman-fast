<?php

/*
 * http协议注解
 * 定义为http协议处理控制器
 * 需要为每个方法配置路由注解数据，可以通过定义的路由伪装代码结构，但会占用存储空间
 */

namespace WorkermanFast\Annotations;

use WorkermanFast\Event;
use Workerman\Protocols\Http;
use GatewayWorker\Lib\Context;
use WorkermanFast\BusinessException;
use Workerman\Protocols\Http\Response;
use Workerman\Connection\TcpConnection;
use WorkermanFast\Protocols\Http\Request;

/**
 * @DefineUse(class=true)
 * @DefineParam(name="path", type="string", default="") 指定路由前段路径
 * @DefineParam(name="mime", type="string", default="") 指定静态文件类型配置文件，默认使用内置
 */
class HttpRouter implements iAnnotation {

    /**
     * @var array 静态文件类型集合
     */
    protected $mimeTypes = [];

    /**
     * 初始化处理
     */
    public function __construct() {
        Http::requestClass(Request::class);
    }

    /**
     * 获取连接处理器
     * @param string $client_id
     * @return TcpConnection|null
     */
    protected function getTcpConnection($client_id) {
        $address_data = Context::clientIdToAddress($client_id);
        if ($address_data) {
            $address = long2ip($address_data['local_ip']) . ":{$address_data['local_port']}";
            return Event::$businessWorker->gatewayConnections[$address];
        }
    }

    /**
     * 加载静态文件类型数据
     * @param string $file
     */
    protected function loadMimeTypes(string $file) {
        if (!$file) {
            $file = __DIR__ . '/mime.types';
        }
        if (file_exists($file) && is_readable($file)) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $data = preg_split('/\s+/', $line);
                if (count($data)) {
                    $type = array_shift($data);
                    foreach ($data as $ext) {
                        $this->mimeTypes[strtolower($ext)] = $type;
                    }
                }
            }
        }
    }

    /**
     * 请求静态文件
     * @param Request $request
     * @return Response
     */
    protected function requestStatic(Request $request) {
        $file = APP_PATH . '/../public/' . $request->uri();
        // 取出文件名后缀
        $ext = strtolower(ltrim(strrchr(basename($file), '.')));
        if (file_exists($file) && isset($this->mimeTypes[$ext])) {
            $result = new Response(200, ['Content-Type' => $this->mimeTypes[$ext]]);
            $result->file = [
                'file' => $file,
                'offset' => 0,
                'length' => 0,
            ];
            return $result;
        }
    }

    /**
     * 注解处理数据生成
     * @param array $params
     * @param array $input
     * @return array
     */
    public function make(array $params, array $input): array {
        $indexs = [];
        foreach ($params as $param) {
            $indexs[$param['path']] = $param['path'];
            $this->loadMimeTypes($param['mime']);
        }
        $parse = $input['parse'];
        $parse->addCall($input['class'], function (array $params)use ($parse) {
            list($client_id, $message) = $params;
            $connection = $this->getTcpConnection($client_id);
            $request = Http::decode($message, $connection);
            try {
                // 静态文件处理
                $result = $this->requestStatic($request);
                if (is_null($result)) {
                    $result = $parse->callIndex('http-router', $request->method() . $request->uri(), $request);
                    if (is_null($result)) {
                        $result = $parse->callIndex('http-router', $request->uri(), $request);
                        if (is_null($result)) {
                            $result = $parse->callIndex('bind-call', 'http', $client_id, $request);
                        }
                    }
                }
            } catch (BusinessException $err) {
                $result = $parse->callIndex('bind-call', 'http', $client_id, $request);
            } catch (\Exception $err) {
                $result = $parse->callIndex('bind-call', 'http', $client_id, $request);
                BusinessWorker::log('[ERROR] ' . $err->getMessage() . PHP_EOL . $err->getTraceAsString());
            }
            if (is_null($result)) {
                return;
            }
            if (!$result instanceof Response) {
                $result = new Response(200, [], $result);
            } elseif (isset($result->file)) {
                $file = $result->file['file'];
                if (!file_exists($file) || is_readable($file)) {
                    $result = new Response(403, null, '403 Forbidden');
                }
            }
            return Http::encode($result);
        });
        return [
            'http' => $indexs,
        ];
    }

}
