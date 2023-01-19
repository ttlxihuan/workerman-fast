<?php

/*
 * Websocket协议注解
 * 定义为Websocket协议处理控制器
 */

namespace WorkermanFast\Annotations;

/**
 * @register_use(name="class", key="path")
 * @register_param(name="path", type="string", default="") 指定路由前段路径
 */
class Websocket implements \WorkermanFast\iAnnotation {

    /**
     * 解析注解数据
     * @param Reflector $ref
     * @param array $params
     * @return array
     */
    public function parse(\Reflector $ref, array $params): array {
        
    }

    /**
     * 应用运行注解处理
     * @param array $params
     * @param array $input
     * @return mixed
     */
    public function run(array $params, array $input) {
        
    }

}
