<?php

/*
 * 服务通信消息合成器
 */

namespace WorkermanFast;

class Message {

    /**
     * 生成信息
     * @param string $msg
     * @param string $code
     * @param array $data
     * @return array
     */
    public static function make(string $msg, string $code, array $data = null) {
        $array = [
            'msg' => $msg,
            'code' => $code,
        ];
        if (is_array($data)) {
            $array['data'] = $data;
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
        return static::make($msg, 'ok', $data);
    }

    /**
     * 生成失败信息
     * @param string $msg
     * @param array $data
     * @return array
     */
    public static function fail(string $msg, array $data = null) {
        return static::make($msg, 'fail', $data);
    }

    /**
     * 生成推送消息
     * @param string $type
     * @param array $data
     * @param string $code
     * @param string $msg
     * @param string $typeKeyName
     * @return array
     */
    public static function push(string $type, array $data, string $code = 'ok', string $msg = '', string $typeKeyName = 'type') {
        $data[$typeKeyName] = $type;
        return static::make($msg, $code, $data);
    }

}
