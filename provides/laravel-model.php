<?php

/*
 * 此文件是 illuminate/database 模块初始处理文件
 * 当安装 illuminate/database 模块时自动或指定加载此文件
 */

use Illuminate\Database\Eloquent;
use Illuminate\Database\Capsule\Manager;
use WorkermanFast\Annotations\Transaction;

if (!class_exists(Manager::class) || !class_exists(Eloquent::class)) {
    return;
}

(function() {

    $manager = new Manager();

    $manager->getContainer()['config']['database.default'] = config('database.default') ?: 'default';

    $start = function($name = null)use($manager) {
        $manager->connection($name)->beginTransaction();
    };
    $commit = function($name = null)use($manager) {
        $manager->connection($name)->commit();
    };
    $rollback = function($name = null)use($manager) {
        $manager->connection($name)->rollBack();
    };

    foreach (config('database.connections') ?: [] as $name => $connection) {
        // 添加连接信息
        $manager->addConnection($connection, $name);
    }
    // 事务处理
    Transaction::addHandle($start, $commit, $rollback);

    // ORM启用
    $manager->bootEloquent();

    // 生成类别名
    class_exists('\DB') || class_alias(Capsule::class, '\DB');
    class_exists('\ProvideModel') || class_alias(\Illuminate\Database\Eloquent::class, '\ProvideModel');
})();

return true;
