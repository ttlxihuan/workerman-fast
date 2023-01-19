<?php

/*
 * Websocket协议注解
 * 定义为Websocket协议处理控制器
 * 需要为每个方法配置路由注解数据，可以通过定义的路由伪装代码结构，但会占用存储空间
 */

namespace WorkermanFast\Annotations;

use GatewayWorker\BusinessWorker;
use WorkermanFast\BusinessException;

/**
 * @DefineUse(class=true)
 * @DefineParam(name="path", type="string", default="") 指定路由前段路径
 * @DefineParam(name="route", type="string", default="type") 指定通信路由键名
 */
class WebsocketRouter implements iAnnotation {

    /**
     * 注解处理数据生成
     * @param array $params
     * @param array $input
     * @return array
     */
    public function make(array $params, array $input): array {
        $indexs = [];
        $routes = [];
        // 数据整理
        foreach ($params as $param) {
            $indexs[$param['path']] = $param['path'];
            $routes[$param['path']] = $param['route'];
        }
        $parse = $input['parse'];
        $ping = config('server.gateway.ping.data');
        $ref = $input['class'];
        $className = $ref->getName();
        $parse->addCall($ref, function (array &$params)use ($parse, $routes, $ping, $className) {
            list($client_id, $message) = $params;
            if ($message === $ping) {
                return;
            }
            $format = $this->getFormat($message);
            $params[0] = $data = $this->decode($format, $message);
            foreach ($routes as $route) {
                if (isset($data[$route])) {
                    try {
                        $result = $parse->callIndex('websocket-router', $data[$route], $client_id, $data);
                        if (is_null($result)) {
                            $result = $parse->callIndex('bind-call', 'websocket', $client_id, $data);
                        }
                    } catch (BusinessException $err) {
                        $result = $parse->callIndex('bind-call', 'websocket', $client_id, $data, $err);
                    } catch (\Exception $err) {
                        $result = $parse->callIndex('bind-call', 'websocket', $client_id, $data, $err);
                        BusinessWorker::log('[ERROR] ' . $err->getMessage() . PHP_EOL . $err->getTraceAsString());
                    }
                    if (is_array($result) || $result instanceof \ArrayAccess) {
                        $result[$route] = $data[$route];
                        return $this->encode($format, $result);
                    }
                }
            }
        });
        return [
            'websocket' => $indexs,
        ];
    }

    /**
     * 获取数据格式类型
     * @param string $message
     * @return string
     */
    protected function getFormat(string $message): string {
        if (preg_match('/^\s*(\{.*\}|\[.*\])\s*$/', $message)) {
            return 'json';
        } elseif (preg_match('/^(<.*>)$/', $message)) {
            return 'xml';
        } else {
            return 'json';
        }
    }

    /**
     * 获取数据格式类型
     * @param string $message
     * @return array
     */
    protected function decode(string $format, $message): array {
        switch ($format) {
            case 'json':
                return (array) json_decode($message, true);
            case 'xml':
                return $this->xmlToArray(simplexml_load_string($message, \SimpleXMLIterator::class));
        }
    }

    /**
     * 获取数据格式类型
     * @param string $message
     * @return string
     */
    protected function encode(string $format, $message): string {
        switch ($format) {
            case 'json':
                return json_encode($message);
            case 'xml':
                return $this->makeXml($message);
        }
    }

    /**
     * xml数据转数组
     * @param type $sxe
     * @return type
     */
    protected function xmlToArray($sxe) {
        $extract = [];
        foreach ($sxe->children() as $key => $value) {
            if (array_key_exists($key, $extract)) {
                if (!isset($extract[$key][0])) {
                    $tmp_extract = $extract[$key];
                    $extract[$key] = [];
                    $extract[$key][0] = $tmp_extract;
                } else
                    $extract[$key] = (array) $extract[$key];
            }
            if ($value->count()) {
                if (isset($extract[$key]) && is_array($extract[$key]))
                    $extract[$key][] = $this->extract($value);
                else
                    $extract[$key] = $this->extract($value);
            } else {
                if (isset($extract[$key]) && is_array($extract[$key]))
                    $extract[$key][] = empty(strval($value)) ? null : strval($value);
                else
                    $extract[$key] = empty(strval($value)) ? null : strval($value);
            }
        }
        return $extract;
    }

    /**
     * 生成XML
     * @param mixed $data
     * @return string
     */
    protected function makeXml($data): string {
        if (is_array($data) || $data instanceof \Iterator) {
            return '<?xml version="1.0" encoding="UTF-8"?>' . $this->arrayToXml($data);
        } else {
            return (string) $data;
        }
    }

    /**
     * 数组转xml数据
     * @param array|Iterator $data
     * @return string
     */
    protected function arrayToXml($data): string {
        $xml = '';
        foreach ($data as $name => $value) {
            if (is_array($value) || $value instanceof \Iterator) {
                $xml .= "<$name>" . $this->arrayToXml($value) . "</$name>";
            } else {
                $xml .= "<$name>$value</$name>";
            }
        }
        return $xml;
    }

}
