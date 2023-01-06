<?php

/*
 * 模型处理基类，所有模型类应该继承此类
 */

namespace App\Models;

/**
 * 加载数据模型模块，注解会自动进行匹配安装的模块并生成\WorkermanFast\Model类别名
 * 如果指定模块均未安装则异常终止程序，加载以注解先后顺序，先匹配安装则先启用，后面的模块将终止启用
 * @use(name="laravel-model")
 * @use(name="doctrine-orm")
 */
abstract class Model extends \WorkermanFast\Model {
    
}
