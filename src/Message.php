<?php

/*
 * 服务通信消息合成器
 */

namespace WorkermanFast;

class Message {

    /**
     * 生成信息
     * @param string $msg
     * @param bool $result
     * @param array $data
     * @return array
     */
    public static function make(string $msg, bool $result = true, array $data = null) {
        $array = [
            'desc' => $msg,
            'result' => $result
        ];
        if (is_array($data)) {
            return array_merge($array, $data);
        }
        return $array;
    }

    /**
     * 生成成功信息
     * @param array $data
     * @param string $msg
     * @return array
     */
    public static function success(array $data = null, string $msg = '') {
        return static::make($msg, true, $data);
    }

    /**
     * 生成失败信息
     * @param string $msg
     * @return array
     */
    public static function fail(string $msg) {
        return static::make($msg, false);
    }

    /**
     * 生成推送消息
     * @param string $type
     * @param array $data
     * @param bool $result
     * @param string $msg
     * @return array
     */
    public static function push(string $type, array $data, bool $result = true, string $msg = '') {
        $data['type'] = $type;
        return static::make($msg, $result, $data);
    }

    /**
     * 消息编码
     * @param mixed $data
     */
    public static function encode($data) {
        if (is_array($data)) {
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        return (string) $data;
    }

    /**
     * 消息解码
     * @param string $data
     */
    public static function decode(string $data) {
        $array = json_decode($data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new BusinessException('消息格式错误');
        }
        return $array;
    }

}
