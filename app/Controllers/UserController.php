<?php

/*
 * 用户处理
 */

namespace App\Controllers;

use App\Message;
use App\Services\UserService;

class UserController extends BaseController {

    /**
     * 登录
     * @param array $params
     * @return mixed
     * 
     * @WebsocketMethod()
     * @UseWmiddleware(name="guest")
     * @Validator(name="username", rules="required|int:1", title="用户名")
     * @Validator(name="password", rules="required|string:3,100", title="用户密码")
     */
    public function login(array $params) {
        $user = UserService::call('login', $params);
        return Message::success($user);
    }

    /**
     * 退出登录
     * @param array $params
     * @return mixed
     * 
     * @WebsocketMethod()
     * @UseWmiddleware(name="auth")
     */
    public function logout(array $params) {
        UserService::call('logout', $params);
        return Message::success();
    }

    /**
     * 注册
     * @param array $params
     * @return mixed
     * 
     * @WebsocketMethod()
     * @UseWmiddleware(name="guest")
     * @Validator(name="username", rules="required|int:1", title="用户名")
     * @Validator(name="password", rules="required|string:3,100", title="用户密码")
     */
    public function register(array $params) {
        $user = UserService::call('register', $params);
        return Message::success($user);
    }

}
