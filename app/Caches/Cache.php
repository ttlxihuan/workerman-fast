<?php

/*
 * 缓存基类，所有缓存类应该继承此类
 */

namespace App\Caches;

use WorkermanFast\Cache;

/**
 * 指定加载缓存模块，注解会自动进行匹配并生成\WorkermanFast\Cache类别名
 * 如果指定模块均未安装则异常终止程序，加载以注解先后顺序，先匹配安装则先启用，后面的模块将终止启用
 * @use(name="predis")
 * @use(name="doctrine-cache")
 */
class Cache {
    
}
