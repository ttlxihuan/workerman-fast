<?php

/*
 * 用户处理服务
 */

namespace App\Services;

use App\Models\UserModel;
use WorkermanFast\BusinessException;

class UserSerivce extends Service {

    /**
     * 通过用户名获取用户数据
     * @param string $username
     * @return mixed
     */
    public function getUserByUsername(string $username) {
        return UserModel::where('username', $username)->get();
    }

    /**
     * 登录操作
     * @param string $cid
     * @param array $params
     * @return string
     */
    public function login(string $cid, array $params) {
        $user = static::call('getUserByUsername', $params['username']);
        if ($user && password_verify($params['password'], $user['password'])) {
            $this->setLogin($user);
        }
        throw new BusinessException('用户不存在或密码错误');
    }

    /**
     * 退出登录操作
     * @param string $cid
     */
    public function logout(string $cid) {
        unset($_SESSION['user']);
    }

    /**
     * 设置登录用户
     * @param UserModel $user
     */
    protected function setLogin(UserModel $user) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
        ];
    }

    /**
     * 注册用户操作
     * @param string $cid
     * 
     * @Transaction()
     */
    public function register(string $cid, array $params) {
        $user = static::call('getUserByUsername', $params['username']);
        if ($user) {
            throw new BusinessException('用户名已经被占用');
        } else {
            $user = new UserModel($params);
            $user->save();
            $this->setLogin($user);
        }
    }

}
