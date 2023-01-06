<?php

/*
 * 长时间静默的连接进行关闭
 */

namespace App\Timers;

class SilenceTimer extends Timer {

    /**
     * 关闭长时间静默的连接
     * @timer(interval=300)
     */
    public function close() {
        
    }

}
