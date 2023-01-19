<?php

/*
 * Websocket协议注解
 * 定义为Websocket协议处理控制器方法
 */

namespace WorkermanFast\Annotations;

/**
 * @register_use(name="function", key="name")
 * @register_param(name="name", type="string") 指定请求名，整个路由是前段路径+请求名，连接时无分隔符
 */
class WebsocketRoute implements \WorkermanFast\iAnnotation {

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
