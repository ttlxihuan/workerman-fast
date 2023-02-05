<?php

/*
 * 用户处理服务
 */

namespace App\Services;

use App\Models\UserModel;
use GatewayWorker\Lib\Gateway;
use GatewayWorker\Lib\Context;
use WorkermanFast\BusinessException;

class UserService extends Service {

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
     * @param array $params
     * @return string
     */
    public function login(array $params) {
        $user = static::call('getUserByUsername', $params['username']);
        if ($user && password_verify($params['password'], $user['password'])) {
            $this->setLogin($user);
        }
        throw new BusinessException('用户不存在或密码错误');
    }

    /**
     * 退出登录操作
     */
    public function logout() {
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
        Gateway::bindUid(Context::$client_id, $user['id']);
    }

    /**
     * 注册用户操作
     * @param array $params
     * 
     * @Transaction()
     */
    public function register(array $params) {
        $user = static::call('getUserByUsername', $params['username']);
        if ($user) {
            throw new BusinessException('用户名已经被占用');
        } else {
            $user = new UserModel($params);
            $user->save();
            $this->setLogin($user);
        }
    }

    /**
     * 更新session
     * @param UserModel $user
     * @param \Closure $callback
     */
    protected function updateSession(UserModel $user, \Closure $callback) {
        // 更新session余额
        if (Gateway::isUidOnline($user['id'])) {
            foreach (Gateway::getClientIdByUid($user['id']) as $cid) {
                $session = $callback(Gateway::getSession($cid));
                if (is_array($session)) {
                    Gateway::setSession($cid, $session);
                }
            }
        }
    }

}
