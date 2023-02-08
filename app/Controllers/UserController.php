<?php

/*
 * 用户处理
 */

namespace App\Controllers;

use App\Message;
use App\Services\UserSerivce;

class UserController extends Controller {

    /**
     * 登录
     * @param string $cid
     * @param array $params
     * @return mixed
     * 
     * @WebsocketMethod()
     * @UseWmiddleware(name="guest")
     * @Validator(name="username", rules="required|int:1", title="用户ID")
     * @Validator(name="password", rules="required|string:3,100", title="用户密码")
     */
    public function login(string $cid, array $params) {
        return UserSerivce::call('login', $cid, $params);
    }

    /**
     * 退出登录
     * @param string $cid
     * @param array $params
     * @return mixed
     * 
     * @WebsocketMethod()
     * @UseWmiddleware(name="auth")
     */
    public function logout(string $cid, array $params) {
        UserSerivce::call('logout', $cid);
        return Message::success();
    }

}
