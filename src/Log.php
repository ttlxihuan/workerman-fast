<?php

/*
 * 日志记录处理
 */

namespace WorkermanFast;

class Log {

    /**
     * 打印错误
     * @param Throwable $previous
     */
    public static function error(\Throwable $previous) {
        static::print('ERROR', $previous->getMessage() . PHP_EOL . $previous->getTraceAsString());
    }

    /**
     * 打印信息
     * @param mixed $msg
     */
    public static function info($msg) {
        static::print('INFO', $msg);
    }

    /**
     * 打印发送信息
     * @param mixed $msg
     * @param string $cid
     */
    public static function send($msg, $cid = null) {
        static::print(($cid ?: Context::$client_id) . ' <<', $msg);
    }

    /**
     * 打印接收信息
     * @param mixed $msg
     * @param string $cid
     */
    public static function receive($msg, $cid = null) {
        static::print(($cid ?: Context::$client_id) . ' >>', $msg);
    }

    /**
     * 打印警告
     * @param mixed $msg
     */
    public static function warn($msg) {
        static::print('WARN', $msg);
    }

    /**
     * 打印指定类型内容
     * @param mixed $msg
     */
    protected static function print(string $type, $msg) {
        if (env('APP_DEBUG')) {
            if (is_array($msg) || is_object($msg)) {
                $msg = var_export($msg, true);
            }
            $date = date('Y-m-d H:i:s');
            echo "[$date][$type]: $msg" . PHP_EOL;
        }
    }

}
