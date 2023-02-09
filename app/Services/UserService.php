<?php

/*
 * 用户处理服务
 */

namespace App\Services;

use App\Models\UserModel;
use GatewayWorker\Lib\Gateway;
use GatewayWorker\Lib\Context;
use WorkermanAnnotation\BusinessException;

class UserService extends Service {

    /**
     * 通过用户名获取用户数据
     * @param string $username
     * @return mixed
     * 
     * @Cache()
     */
    public function getByUsername(string $username) {
        return UserModel::where('username', $username)->first();
    }

    /**
     * 登录操作
     * @param array $params
     * @return string
     */
    public function login(array $params) {
        $user = static::call('getByUsername', $params['username']);
        if ($user && password_verify($params['password'], $user['password'])) {
            return $this->setLogin($user);
        } else {
            throw new BusinessException('用户不存在或密码错误');
        }
    }

    /**
     * 退出登录操作
     * @param array $params
     */
    public function logout(array $params) {
        unset($_SESSION['user']);
    }

    /**
     * 设置登录用户
     * @param UserModel $user
     * @return array
     */
    protected function setLogin(UserModel $user): array {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
        ];
        Gateway::bindUid(Context::$client_id, $user['id']);
        return $_SESSION['user'];
    }

    /**
     * 注册用户操作
     * @param array $params
     * 
     * @Transaction()
     */
    public function register(array $params) {
        $user = static::call('getByUsername', $params['username']);
        if ($user) {
            throw new BusinessException('用户名已经被占用');
        } else {
            $user = new UserModel();
            $user->username = $params['username'];
            $user->password = password_hash($params['password'], PASSWORD_BCRYPT);
            $user->save();
            return $this->setLogin($user);
        }
    }

}
