<?php

/*
 * 业务异常
 */

namespace WorkermanFast;

use GatewayWorker\Lib\Gateway;

class BusinessException extends \Exception {

    /**
     * @var string 异常类型
     */
    protected $type = 'exitgame';

    /**
     * 创建异常
     * @param string $message
     * @param \Throwable $previous
     * @return \Exception
     */
    public function __construct(string $message, \Throwable $previous = NULL) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * 转换异常对象
     * @param \Throwable $throwable
     * @param string $type
     * @param string $message
     * @return \static|\self
     */
    public static function convert(\Throwable $throwable, string $type, string $message = null) {
        if (!$throwable instanceof self) {
            Log::error($throwable);
            $throwable = new static($message ?: $throwable->getMessage());
        }
        $throwable->type = $type;
        return $throwable;
    }

    /**
     * 发送给当前终端
     */
    public function sendToCurrentClient() {
        Gateway::sendToCurrentClient($send = $this->getRespond());
        Log::send($send);
        if ($this->type == 'exitgame') {
            Gateway::closeCurrentClient();
        }
    }

    /**
     * 获取异常响应内容
     * @return string
     */
    public function getRespond() {
        $data = Message::fail($this->message);
        $data['type'] = $this->type;
        return Message::encode($data);
    }

}
