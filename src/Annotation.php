<?php

/*
 * 注解处理类
 * 注解分析并记录下来，方便后面提取操作
 * 类注解使用：
 *  1、注册注解名、键名
 *  2、定义注解基础数据，可合并到方法注解中
 * 方法注解使用：
 *  1、定义注册的注解名，并指定相关参数
 * 
 * 类注解用来定义方法中可使用的注解体，并且能继承上级类定义，属于叠加定义
 * 方法注解用来提取注解操作数据，通过类中定义的键名进行存储
 * 
 * 所有注解开始是 
 *      @register(name="annotation", key="type", attach="")
 *          name    注册需要使用的注解名，此名为可使用的注解名，需要保存命名规则（与PHP命令规则一至），name = register 无效
 *          key     注册注解存储键名，此名为提取可使用注解名定义的参数名，将对应的参数值作为存储键名，方便提取
 *          attach  注解附加到注解数据上，如果指定数据不存在则此注解无效
 *      此注册表示当前类及子类的方法均可使用注解 @annotation(type="键名值")
 *      如果不指定type则为全局数据（即所有type均能提取）
 *      也可以指定其它参数，并且不限制参数个数，比如：@annotation(type="键名值", name=2, return=false)
 * 
 *      注解最终参数功能受各模块限制：
 *          Timer
 *              定时器注解，注解名由注册决定
 *              类默认注册：
 *                  @register(name="timer", key="id")
 *                  @timer(id=0, interval=1, persistent=true)
 *              方法注解参数：
 *                  @timer(id=0, interval=1, persistent=true)
 *                      id          定时器启动进程ID，建议使用0，否则win系统下无法启动
 *                      interval    定时间隔时长，默认为1s
 *                      persistent  是否为持久定时，默认true
 * 
 *          Controller
 *              控制器注解，注解名由注册决定
 *              类默认注册：
 *                  @register(name="request", key="type")
 *                  @register(name="useWmiddleware", key="action")
 *              方法注解参数：
 *                  @request(type="", prefix="")
 *                      type        请求类型名，如同url地址
 *                      prefix      类型名前缀，主要给类注解使用，类中指定下面的方法均会携带
 * 
 *                  @useWmiddleware(action="")
 *                      action      使用中间件动作名，与中间件定义相同即可
 * 
 *          Middleware
 *              中间件注解，注解名由注册决定
 *              类默认注册：
 *                  @register(name="wmiddleware", key="action")
 *              方法注解参数：
 *                  @wmiddleware(action="")
 *                      action      指定中间件动作名，动作名主要有各件事和控制器中定义的动作名，中间件在其它处理前调用，并且可以中断后续执行
 * 
 * 类中定义的注解信息可用来填充方法中共用的参数，并且能继承上级类定义，注册的注解参数可以自由增减（默认参数需要保留），在调用对应方法时可以获取到注解参数
 * 多个注解表示多个记录，即使相同的注解
 * 
 */

namespace WorkermanFast;

use Exception;
use ReflectionClass;

class Annotation {

    /**
     * @var array 注解专用项集
     */
    protected $items = [];

    /**
     * @var array 注解通用项集
     */
    protected $global = [];

    /**
     * 初始化
     * @param string $baseClass
     * @param string $baseNamespace
     * @param string $path
     */
    public function __construct(string $baseClass, string $baseNamespace = null, string $path = null) {
        if (is_null($path)) {
            $array = explode('\\', $baseClass);
            $childPath = end($array) . 's';
            $path = __DIR__ . '/' . $childPath;
            $baseNamespace = preg_replace('#[/\\\\][^/\\\\]+$#', '', $baseClass) . '\\' . $childPath;
        }
        $this->load($baseClass, $baseNamespace, $path);
    }

    /**
     * 加载注解
     * @param string $baseClass
     * @param string $baseNamespace
     * @param string $path
     * @throws Exception
     */
    public function load(string $baseClass, string $baseNamespace, string $path) {
        if (!class_exists($baseClass)) {
            throw new Exception('要加载的注解基类 ' . $baseClass . ' 不存在');
        }
        $baseNamespace = rtrim($baseNamespace, '\\') . '\\';
        if (is_dir($path) && file_exists($path) && is_readable($path)) {
            $array = explode('\\', $baseClass);
            $suffix = end($array);
            $children = [];
            $basePath = realpath($path);
            foreach (glob($basePath . '/*' . $suffix . '.php') as $file) {
                $childClass = rtrim($baseNamespace . str_replace([$basePath, '/'], ['', '\\'], dirname($file)), '\\') . '\\' . basename($file, '.php');
                if (trim($childClass, '\\') === trim($baseClass, '\\')) {
                    continue;
                }
                if (class_exists($childClass) && is_subclass_of($childClass, $baseClass)) {
                    $ref = new ReflectionClass($childClass);
                    if (!$ref->isTrait() && !$ref->isInterface()) {
                        $children[$childClass] = $ref;
                    }
                } else {
                    throw new Exception('要加载的注解类 ' . $childClass . ' 不存在或不继承基类： ' . $baseClass);
                }
            }
            $this->extract(new ReflectionClass($baseClass), $children);
        } else {
            throw new Exception('要加载的注解目录 ' . $path . ' 不能正常访问');
        }
    }

    /**
     * 获取注册定义数据体
     * @param ReflectionClass $class
     * @param array $base
     * @return array
     */
    protected function getRegister(ReflectionClass $class, array $base = []) {
        $params = $this->parse($class);
        foreach ($base as $name => $items) {
            $params[$name] = array_merge($items, $params[$name] ?? []);
        }
        // 整理注册数据
        if (isset($params['register'])) {
            $alone = [];
            $attach = [];
            foreach ($params['register'] as $register) {
                if (isset($register['attach'])) {
                    $attach[$register['attach']][] = $register;
                } else {
                    $alone[] = $register;
                }
            }
            foreach ($alone as &$register) {
                if (isset($attach[$register['name']])) {
                    $register['attaches'] = $attach[$register['name']];
                }
            }
            $params['register'] = $alone;
        }
        return $params;
    }

    /**
     * 生成注解数据
     * @param ReflectionClass $class
     * @param array $base
     */
    protected function make(ReflectionClass $class, array $base = []) {
        $instance = $class->newInstance();
        $params = $this->getRegister($class, $base);
        // 相近父类是当前指定的基类直接处理
        foreach ($class->getMethods() as $refMethod) {
            if ($refMethod->isPublic() && !$refMethod->isConstructor() && !$refMethod->isDestructor() && !$refMethod->isStatic()) {
                $data = $this->parse($refMethod);
                foreach ($params['register'] ?? [] as $register) {
                    $name = $register['name'] ?? null;
                    if (empty($name) || $name == 'register') {
                        continue;
                    }
                    $attaches = [];
                    foreach ($register['attaches'] ?? [] as $attach) {
                        $attachName = $attach['name'] ?? null;
                        if (empty($attachName) || $attachName == 'register') {
                            continue;
                        }
                        foreach ($data[$attachName] ?? [] as $item) {
                            if (isset($params[$attachName])) {
                                foreach ($params[$attachName] as $bItem) {
                                    $attaches[$attachName][] = array_merge($bItem, $item);
                                }
                            } else {
                                $attaches[$attachName][] = $item;
                            }
                        }
                    }
                    $key = $register['key'] ?? null;
                    $array = [];
                    foreach ($data[$name] ?? [] as $item) {
                        $item['@call'] = [$instance, $refMethod->getName()];
                        $item['@attaches'] = $attaches;
                        if (isset($params[$name])) {
                            foreach ($params[$name] as $bItem) {
                                $mergeKey = ($bItem[$key] ?? '') . ($item[$key] ?? '');
                                $array[$mergeKey][] = array_merge($bItem, $item, is_null($key) ? [] : [$key => $mergeKey]);
                            }
                        } else {
                            $array[$item[$key] ?? ''][] = $item;
                        }
                    }
                    foreach ($array as $key => $items) {
                        if ($key === '' || $key === '*') {
                            $this->global[$name] = array_merge($this->global[$name] ?? [], $items);
                        } else {
                            $this->items[$name][$key] = array_merge($this->items[$name][$key] ?? [], $items);
                        }
                    }
                }
            }
        }
    }

    /**
     * 提取注解数据
     * @param ReflectionClass $baseRef
     * @param array $childrenRef
     * @param array $base
     * @return int
     */
    protected function extract(ReflectionClass $baseRef, array &$childrenRef, array $base = []) {
        $count = 0;
        $params = $this->getRegister($baseRef, $base);
        foreach ($childrenRef as $index => $ref) {
            if (!is_subclass_of($ref->getName(), $baseRef->getName())) {
                continue;
            }
            // 提取相近的父类进行解析
            $parent = $ref->getParentClass();
            $existParent = null;
            if ($parent->getName() != $baseRef->getName()) {
                do {
                    // 提取存在解析的上级
                    $existParent = $childrenRef[$parent->getName()] ?? $existParent;
                } while ($parent = $parent->getParentClass());
                if ($existParent) {
                    unset($childrenRef[$existParent->getName()]);
                    $this->extract($existParent, $childrenRef, $params);
                    continue;
                }
            }
            // 解析库中没有下级
            if (!$this->extract($ref, $childrenRef, $params) && $ref->isInstantiable()) {
                unset($childrenRef[$index]);
                $this->make($ref, $params);
            }
        }
        return $count;
    }

    /**
     * 解析注解内容
     * @param Reflector $ref
     * @return array
     */
    protected function parse(\Reflector $ref) {
        $doc = $ref->getDocComment();
        $array = [];
        $nameReg = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';
        $valueReg = '([\+\-]?\d+(\.\d+)?|true|false|null|"([^"]+|\\\\\.)*"|\'[^\']*\')';
        if (preg_match_all("/@{$nameReg}\s*\(\s*({$nameReg}\s*=\s*{$valueReg}\s*(,\s*{$nameReg}\s*=\s*{$valueReg}\s*)*)?\)/i", $doc, $matches)) {
            foreach ($matches[0] as $item) {
                if (!preg_match("/^@({$nameReg})\s*\(/i", $item, $names) || !preg_match_all("/({$nameReg})\s*=\s*({$valueReg})/i", $item, $params)) {
                    continue;
                }
                $name = $names[1];
                $items = [];
                foreach ($params[1] as $key => $param) {
                    $value = $params[2][$key];
                    switch ($value[0]) {
                        case '"':
                            $value = preg_replace('/\\\\(.)/', '$1', $params[2][$key]);
                        case "'":
                            $value = substr($value, 1, -1);
                            break;
                        default:
                            switch ($value) {
                                case 'true':
                                    $value = true;
                                    break;
                                case 'false':
                                    $value = false;
                                    break;
                                case 'null':
                                    $value = null;
                                    break;
                                default:
                                    if (strpos($value, '.') !== false) {
                                        $value = intval($value);
                                    } else {
                                        $value = floatval($value);
                                    }
                                    break;
                            }
                    }
                    $items[$param] = $value;
                }
                $array[$name][] = $items;
            }
        }
        // 核验处理，注解语法错误将终止程序
        if (preg_match_all("/@{$nameReg}\s*\(.*?\)/i", $doc, $tags) && count($tags[0]) != count($matches[0])) {
            $data = $matches[0] ?? [];
            foreach ($tags[0] as $tag) {
                foreach ($data as $key => $item) {
                    if (strpos($item, $tag) === 0) {
                        unset($data[$key]);
                        continue 2;
                    }
                }
                if ($ref instanceof ReflectionClass) {
                    throw new BusinessException('类注解语法错误:' . $ref->getName() . ' ' . $tag);
                } else {
                    throw new BusinessException('方法注解语法错误:' . $ref->getDeclaringClass()->getName() . '::' . $ref->getName() . ' ' . $tag);
                }
            }
        }
        return $array;
    }

    /**
     * 获取注解数据
     * @param string $name
     * @param string $key
     * @return array
     */
    public function get(string $name, string $key = null): array {
        $global = $this->global[$name] ?? [];
        $items = $this->items[$name] ?? [];
        if (is_null($key)) {
            if (count($global)) {
                $items['*'] = $global;
            }
            return $items;
        }
        return array_merge($items[$key] ?? [], $global);
    }

    /**
     * 获取注解数量
     * @return int
     */
    public function count(string $name): int {
        return count($this->items[$name] ?? []) + count($this->global[$name] ?? []);
    }

}
