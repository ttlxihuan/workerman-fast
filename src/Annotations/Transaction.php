<?php

/*
 * 数据库事件处理注解
 */

namespace WorkermanFast\Annotations;

use Closure;

/**
 * @DefineUse(function=true)
 * @DefineParam(name="name", type="string", default="") 指定事务处理配置名，不指定为默认数据库连接
 */
class Transaction implements iAnnotation {

    /**
     * @var bool 事务全局标识
     */
    private static $transaction = [];

    /**
     * @var bool 事务操作处理器
     */
    private static $handles = [];

    /**
     * 添加数据库操作处理器
     * @param string $name
     * @param Closure $start
     * @param Closure $commit
     * @param Closure $rollback
     */
    public static function addHandle(Closure $start, Closure $commit, Closure $rollback) {
        static::$handles = compact('start', 'commit', 'rollback');
    }

    /**
     * 注解处理数据生成
     * @param array $params
     * @param array $input
     * @return array
     */
    public function make(array $params, array $input): array {
        $names = array_column($params, 'name');
        return [
            function(array $params, Closure $next) use($names) {
                try {
                    static::handle('start', ...$names);
                    $res = $next();
                    static::handle('commit', ...$names);
                    return $res;
                } catch (\Exception $err) {
                    static::handle('commit', ...$names);
                    throw $err;
                }
            }
        ];
    }

    /**
     * 调用处理事件
     * @param string $action
     * @param string $names
     */
    public static function handle(string $action, string ...$names) {
        if (count(static::$handles)) {
            $tag = $action == 'start';
            foreach ($names as $name) {
                if (empty(static::$transaction[$name])) {
                    static::$handles[$action]($name);
                    static::$transaction[$name] = $tag;
                }
            }
        }
    }

}
