<?php

/*
 * 用户处理
 */

namespace App\Controllers;

class UserController extends Controller {

    /**
     * 登录
     * @param string $cid
     * @param array $params
     * @return mixed
     * 
     * @request(type="login")
     * @useWmiddleware(action="guest")
     * @validator(name="username", rules="required|int:1", title="用户ID")
     * @validator(name="password", rules="required|string:3,100", title="用户密码")
     */
    public function login(string $cid, array $params) {
        $result = UserSerivce::call('login', $params['username'], $params['password']);
        $result['cid'] = $cid;
        return $result;
    }

    /**
     * 退出登录
     * @param string $cid
     * @param array $params
     * @return mixed
     * 
     * @request(type="logout")
     * @useWmiddleware(action="auth")
     */
    public function logout(string $cid, array $params) {
        UserSerivce::call('logout', $cid);
        return Message::success();
    }

}
