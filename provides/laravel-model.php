<?php

/*
 * 此文件是 illuminate/database 模块初始处理文件
 * 当安装 illuminate/database 模块时自动或指定加载此文件
 */

use Illuminate\Database\Capsule\Manager;
use WorkermanAnnotation\Annotations\Transaction;
use Illuminate\Database\Eloquent\Model as Eloquent;

if (!class_exists(Manager::class) || !class_exists(Eloquent::class)) {
    return;
}

(function() {
    $manager = new Manager();
    $container = $manager->getContainer();
    $container['config']['database.default'] = workerConfig('database.default', 'default');

    foreach (workerConfig('database.connections') ?: [] as $name => $connection) {
        // 添加连接信息
        $manager->addConnection($connection, $name);
    }
    // 事务注解处理
    $start = function($name = null)use($manager) {
        $manager->connection($name)->beginTransaction();
    };
    $commit = function($name = null)use($manager) {
        $manager->connection($name)->commit();
    };
    $rollback = function($name = null)use($manager) {
        $manager->connection($name)->rollBack();
    };
    Transaction::addHandle($start, $commit, $rollback);
    // 写到全局
    $manager->setAsGlobal();
    // ORM启用
    $manager->bootEloquent();

    // 生成类别名
    class_exists('\\DB') || class_alias(Manager::class, '\\DB');
    class_exists('\\Model') || class_alias(Eloquent::class, '\\Model');
})();

return true;
