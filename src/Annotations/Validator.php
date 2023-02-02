<?php

/*
 * 验证注解处理
 */

namespace WorkermanFast\Annotations;

use WorkermanFast\Validator as ValidatorRun;

/**
 * @DefineUse(function=true, class=true)
 * @DefineParam(name="name", type="string")  验证字段名
 * @DefineParam(name="value", type="mixed") 默认值
 * @DefineParam(name="rules", type="string") 验证规则
 * @DefineParam(name="title", type="string", default="") 验证字段标题名，不指定则为字段名
 */
class Validator implements iAnnotation {

    /**
     * 注解处理数据生成
     * @param array $params
     * @param array $input
     * @return array
     */
    public function make(array $params, array $input): array {
        return [
            function(array &$call_params, \Closure $next)use($params) {
                ValidatorRun::adopt($call_params[count($call_params) - 1], $params);
                return $next();
            }
        ];
    }

}
