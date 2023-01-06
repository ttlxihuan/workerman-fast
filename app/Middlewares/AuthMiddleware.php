<?php

/*
 * 授权中间件
 */

namespace App\Middlewares;

class AuthMiddleware extends Middleware {

    /**
     * 访客验证
     * @wmiddleware(action="guest")
     */
    public function guest() {
        if (isset($_SESSION['user'])) {
            throw new BusinessException('您已经登录');
        }
    }

    /**
     * 授权验证
     * @wmiddleware(action="auth")
     */
    public function auth() {
        if (empty($_SESSION['user'])) {
            throw new BusinessException('请登录');
        }
    }

}
