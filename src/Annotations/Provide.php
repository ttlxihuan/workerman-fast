<?php

/*
 * 三方外部扩展包注解处理
 */

namespace WorkermanFast\Annotations;

/**
 * @DefineUse(class=true)
 * @DefineParam(name="action", type="string") 指定外部扩展功能名，调用入口
 * @DefineParam(name="name", type="string") 指定外部扩展处理名
 */
class Provide implements iAnnotation {

    /**
     * @var array 提供记录暂存
     */
    private static $provides = [];

    /**
     * 注解处理数据生成
     * @param array $params
     * @param array $input
     * @return array
     */
    public function make(array $params, array $input): array {
        $parse = $input['parse'];
        $class = $input['class'];
        foreach ($params as $param) {
            $name = $param['name'];
            $parse->addCallIndex($class, $param['action'], function()use($class, $name, $parse) {
                $parse->call($class, $name);
            });
        }
        $parse->addCall($class, function($name) {
            if (empty(static::$provides[$name])) {
                $file = APP_PATH . "/../provides/{$name}.php";
                if (file_exists($file) && is_readable($file)) {
                    static::$provides[$name] = include $file;
                }
            }
            if (isset(static::$provides[$name])) {
                return static::$provides[$name];
            }
        });
        return [];
    }

}
