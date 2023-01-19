<?php

/*
 * http协议注解
 * 定义为http协议处理控制器方法
 */

namespace WorkermanFast\Annotations;

/**
 * @DefineUse(function=true)
 * @DefineParam(name="type", type="string", default="") 限制请求类型，多个可以使用逗号分开，为空则所有请求类型
 * @DefineParam(name="name", type="string", default="") 指定请求名（不指定为当前方法名），整个路由是前段路径+请求名，连接时无分隔符
 */
class HttpMethod implements iAnnotation {

    /**
     * @var array 路由信息
     */
    protected $router = [];

    /**
     * 注解处理数据生成
     * @param array $params
     * @param array $input
     * @return array
     */
    public function make(array $params, array $input): array {
        $indexs = [];
        foreach ($input['indexs']['http'] ?? [''] as $before) {
            foreach ($params as $param) {
                if (empty($param['type'])) {
                    $indexs[] = $before . $param['name'];
                    continue;
                }
                foreach (explode(',', $param['type']) as $type) {
                    if (in_array($param['type'], ['GET', 'POST', 'HEAD', 'DELETE', 'PUT', 'OPTION'], true)) {
                        $indexs[] = $param['type'] . $before . $param['name'];
                    }
                }
            }
        }
        return [
            'http-router' => $indexs
        ];
    }

}
