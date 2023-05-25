<?php

/*
 * 授权中间件
 */

namespace App\Middlewares;

use WorkermanAnnotation\BusinessException;

class AuthMiddleware extends Middleware {

    /**
     * 访客验证
     * @Middleware()
     */
    public function guest() {
        if (isset($_SESSION['user'])) {
            throw new BusinessException('您已经登录');
        }
    }

    /**
     * 授权验证
     * @Middleware()
     */
    public function auth() {
        if (empty($_SESSION['user'])) {
            throw new BusinessException('请登录');
        }
    }

}
