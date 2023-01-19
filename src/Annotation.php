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
 *      
 *    注册注解必需使用在基类上（注册必需定义在类上，不可使用在方法上），其所有子类将生效注册的注解，注册的注解只会向下复用（子类定义的注解不可使用在父类上）
 *    注册顺序会影响应用时调用顺序，一般建议最后调用的注解必需注册在最下面，相同注解名只能注册一次（当前类及其子类范围中）
 * 
 *      【注解使用】
 *      注册可使用注解处理类，使用时用类名（去掉命令空间）
 *      @Register(class="")
 *          class   定义注解应用时处理类，类必需继承相关接口
 * 
 *      【注解应用处理类】
 *      注册注解应用处理器可使用位置
 *      @DefineUse(function=true, class=true, key="name")
 *          function    指定是否能在方法中使用，默认不可以
 *          class       指定是否能在类中使用，默认不可以
 *      注解应用处理类定义参数
 *      @DefineParam(name="", type="", default="")
 *          name    指定参数名，同一个注解应用处理类下参数名不可重复
 *          type    限制这个参数的数据类型（不指定则可为任何类型），暂时只支持：string、int、float、bool
 *          default 指定默认值，不指定则必需在使用注解时指定值
 * 
 * 
 *    1、注解解析将生成的数据写入注解解析对象中，将在注解解析对象中创建注解应用处理对象进行使用
 *    2、在要使用注解的地方创建注解解析处理器，使用时直接进行调用即可，注解解析处理对象会按顺序调用内部的注解应用处理对象，当处理对象发出异常时会终止向下运行注解应用处理器
 * 
 * 
 * 类中定义的注解信息可用来填充方法中共用的参数，并且能继承上级类定义，注册的注解参数可以自由增减（默认参数需要保留），在调用对应方法时可以获取到注解参数
 * 多个注解表示多个记录，即使相同的注解
 * 
 * 类注解处理
 * 1、添加全局单一处理函数
 * 2、绑定到每个函数的处理函数
 * 3、绑定到每个函数的索引数据
 * 4、添加全局单一索引数据
 * 
 * 函数注解处理
 * 1、绑定给函数的处理函数
 * 2、绑定给函数的索引数据
 * 3、函数需要嵌套包含，方便可以在处理函数内部向下调用

 */

namespace WorkermanFast;

use Exception;
use Reflector;
use ReflectionClass;
use ReflectionMethod;
use WorkermanFast\Annotations\iAnnotation;

class Annotation {

    /**
     * @var array 回调集合，通过方法名调用
     */
    protected $callbacks = [];

    /**
     * @var array 索引集合，通过索引调用
     */
    protected $indexes = [];

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
     * 获取默认注册注解应用处理器
     * @return array
     */
    protected function getDefaultRegisterUses(): array {
        return [
            // @Register(name="timer", class="")
            'Register' => [
                'class' => true,
                'params' => [
                    'class' => ['type' => 'string'],
                ],
                'instance' => new Annotations\Register(),
            ],
        ];
    }

    /**
     * 获取默认定义注解应用处理器
     * @return array
     */
    protected function getDefaultDefineUses(): array {
        return [
            // @DefineUse(function=true, class=true, key="name")
            'DefineUse' => [
                'class' => true,
                'params' => [
                    'function' => ['type' => 'bool', 'default' => false],
                    'class' => ['type' => 'bool', 'default' => false],
                ],
                'instance' => new Annotations\DefineUse()
            ],
            // @DefineParam(name="", type="", default="")
            'DefineParam' => [
                'class' => true,
                'params' => [
                    'name' => ['type' => 'string'],
                    'type' => ['type' => 'string'],
                    'default' => [],
                ],
                'instance' => new Annotations\DefineParam()
            ],
        ];
    }

    /**
     * 加载注解
     * @param string $baseClass
     * @param string $baseNamespace
     * @param string $path
     * @throws Exception
     */
    protected function load(string $baseClass, string $baseNamespace, string $path) {
        if (!class_exists($baseClass)) {
            throw new Exception('要加载的注解基类 ' . $baseClass . ' 不存在');
        }
        $baseNamespace = rtrim($baseNamespace, '\\') . '\\';
        if (is_dir($path) && file_exists($path) && is_readable($path)) {
            $array = explode('\\', $baseClass);
            $suffix = end($array);
            $children = [];
            $basePath = realpath($path);
            foreach (glob("{$basePath}/*{$suffix}.php'") as $file) {
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
            $this->extractParentClass(new ReflectionClass($baseClass), $children);
        } else {
            throw new Exception('要加载的注解目录 ' . $path . ' 不能正常访问');
        }
    }

    /**
     * 从父类开始向下提取注解数据
     * @param ReflectionClass $baseRef
     * @param array $childrenRef
     * @param array $uses
     * @param array $data
     * @return int
     */
    protected function extractParentClass(ReflectionClass $baseRef, array &$childrenRef, array $uses = [], array $data = []): int {
        $items = $this->apply($baseRef, $uses, $data);
        $count = 0;
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
                    $this->extractParentClass($existParent, $childrenRef, $uses, $items);
                    continue;
                }
            }
            // 解析库中没有下级
            if ($ref->isInstantiable() && !$this->extractParentClass($ref, $childrenRef, $uses)) {
                unset($childrenRef[$index]);
                $this->extractClass($ref, $uses, $items);
                $count++;
            }
        }
        return $count;
    }

    /**
     * 从类开始向下提取注解数据
     * @param ReflectionClass $class
     * @param array $uses
     * @param array $data
     */
    protected function extractClass(ReflectionClass $class, array $uses = [], array $data = []) {
        $object = $class->newInstance();
        $items = $this->apply($class, $uses, $data);
        // 剥离数据，索引类、回调类
        $callbacks = [];
        $indexes = [];
        foreach ($this->callMake($uses, $items, ['class' => $class, 'parse' => $this]) as $index => $item) {
            if ($item instanceof \Closure) {
                $callbacks[] = $item;
            } else {
                $indexes[$index] = $item;
            }
        }
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $refMethod) {
            if ($refMethod->isConstructor() || $refMethod->isDestructor() || $refMethod->isStatic()) {
                continue;
            }
            $name = $this->getRefName($refMethod);
            foreach ($callbacks as $callback) {
                $this->callbacks[$name][] = $callback;
            }
            $this->extractFunction($object, $refMethod, $uses, $indexes);
        }
    }

    /**
     * 从方法中提取注解数据
     * @param object $object
     * @param ReflectionMethod $refMethod
     * @param array $uses
     * @param array $indexes
     */
    protected function extractFunction(object $object, ReflectionMethod $refMethod, array $uses = [], array $indexes = []) {
        $name = $this->getRefName($refMethod);
        foreach ($this->callMake($uses, $this->apply($refMethod, $uses), ['indexs' => $indexes, 'method' => $name, 'parse' => $this]) as $index => $item) {
            if ($item instanceof \Closure) {
                $this->callbacks[$name][] = $item;
            } else {
                foreach ($item as $path) {
                    $this->indexes[$index][$path][] = $name;
                }
            }
        }
        $method = $refMethod->getName();
        $this->callbacks[$name][] = function (array $params)use ($object, $method) {
            return $object->$method(...$params);
        };
    }

    /**
     * 注解应用处理
     * @param Reflector $ref
     * @param array $uses
     * @param array $data
     * @return array
     * @throws Exception
     */
    protected function apply(Reflector $ref, array &$uses, array $data = []): array {
        static $registerUses = null;
        if (empty($registerUses)) {
            $registerUses = $this->getDefaultRegisterUses();
        }
        // 注解定义位置名
        $annotations = $this->parse($ref);
        // 注册定义处理
        $registers = $this->convert($ref, $annotations, $registerUses, $data);
        try {
            // 执行生成注解定义数据，合并注解应用处理
            foreach ($this->callMake($registerUses, $registers, ['parse' => $this]) as $name => $params) {
                if (isset($registerUses[$name]) || isset($uses[$name])) {
                    throw new Exception("注册注解名 $name 已经占用");
                }
                $uses[$name] = $params;
            }
        } catch (Exception $err) {
            throw new Exception($this->getRefName($ref) . ' ' . $err->getMessage());
        }
        // 常规注解应用处理类
        $items = $this->convert($ref, $annotations, $uses, $data);
        if (count($annotations)) {
            throw new Exception($this->getRefName($ref) . " 未知注解 " . implode('、', array_keys($annotations)));
        }
        return $items;
    }

    /**
     * 解析注解处理类定义信息
     * @staticvar type $defineUses
     * @param string $class
     * @return array
     * @throws Exception
     */
    public function parseDefine(string $class): array {
        static $defineUses = null;
        if (empty($defineUses)) {
            $defineUses = $this->getDefaultDefineUses();
        }
        if (class_exists($class)) {
            if (!$class instanceof iAnnotation) {
                throw new Exception("注册注解处理类 $class 未继承注解处理接口 " . iAnnotation::class);
            }
            $ref = new ReflectionClass($class);
            $annotations = $this->parse($ref);
            $data = $this->callMake($defineUses, $this->convert($ref, $annotations, $defineUses));
            if (count($annotations)) {
                throw new Exception('注解处理类 ' . $this->getRefName($ref) . " 使用未知注解 " . implode('、', array_keys($annotations)));
            }
            return $data;
        } else {
            throw new Exception("注册注解处理类 $class 不存在");
        }
    }

    /**
     * 按定义注解信息转换注解数据
     * @param Reflector $ref
     * @param array $annotations
     * @param array $defines
     * @param array $data
     * @return array
     * @throws Exception
     */
    protected function convert(Reflector $ref, array &$annotations, array $defines, array $data = []): array {
        // 注册定义应用处理
        foreach ($defines as $name => $define) {
            if (empty($annotations[$name])) {
                continue;
            }
            if (empty($define['class']) && $ref instanceof ReflectionClass) {
                throw new Exception($this->getRefName($ref) . " 指定了不可在定义类的位置使用的注解 $name");
            }
            if (empty($define['function']) && $ref instanceof ReflectionMethod) {
                throw new Exception($this->getRefName($ref) . " 指定了不可在定义方法的位置使用的注解 $name");
            }
            // 注解应用处理
            if (empty($define['instance']) || !is_object($define['instance'])) {
                throw new Exception($this->getRefName($ref) . " 注解 $name 应用处理类错误");
            }
            $define_params = $define['params'] ?: [];
            foreach ($annotations[$name] as $params) {
                $parameters = [];
                foreach ($define_params as $attrname => $param) {
                    if (isset($params[$attrname])) {
                        $value = $params[$attrname];
                        unset($params[$attrname]);
                    } elseif (isset($param['default'])) {
                        $value = $param['default'];
                    } else {
                        throw new Exception($this->getRefName($ref) . " 注解 $name 必需指定属性 $attrname");
                    }
                    if (empty($param['type']) || gettype($value) == $param['type']) {
                        $parameters[$attrname] = $value;
                    } else {
                        throw new Exception($this->getRefName($ref) . " 注解 $name 属性 $attrname 数据类型必需是 {$param['type']}");
                    }
                }
                if (count($params)) {
                    throw new Exception($this->getRefName($ref) . " 注解 $name 指定了未知属性 " . implode('、', array_keys($params)));
                }
                $data[$name][] = $parameters;
            }
            unset($annotations[$name]);
        }
        return $data;
    }

    /**
     * 注解数据生成处理调用
     * @param array $defines
     * @param array $params
     * @param array $input
     * @return array
     */
    protected function callMake(array $defines, array $params, array $input = []): array {
        $result = [];
        foreach ($defines as $name => $define) {
            if (empty($params[$name])) {
                continue;
            }
            $result = array_merge($result, $define['instance']->make($params[$name], $input));
        }
        return $result;
    }

    /**
     * 添加注解调用
     * @param Reflector $ref
     * @param \Closure $callback
     */
    public function addCall(Reflector $ref, \Closure $callback) {
        $this->callbacks[$this->getRefName($ref)][] = $callback;
    }

    /**
     * 添加注解索引
     * @param string $name
     * @param string $index
     * @param Reflector $ref
     */
    public function addCallIndex(string $name, string $index, Reflector $ref) {
        $this->indexes[$name][$index][] = $this->getRefName($ref);
    }

    /**
     * 调用注解函数
     * @param string $name
     * @param array $params
     * @return mixed
     */
    public function call(string $name, ...$params) {
        $callbacks = $this->callbacks[$name] ?? [];
        $next = function()use(&$callbacks, &$next, &$params) {
            if ($callback = array_shift($callbacks)) {
                return $callback($params, $next);
            }
        };
        return $next();
    }

    /**
     * 通过注解索引调用注解函数
     * @param string $name
     * @param string $index
     * @param array $params
     * @return mixed
     */
    public function callIndex(string $name, string $index, ...$params) {
        foreach ($this->indexes[$name][$index] ?? [] as $method) {
            if (is_null($result = $this->call($method, ...$params))) {
                continue;
            }
            return $result;
        }
    }

    /**
     * 解析注解内容
     * @param Reflector $ref
     * @return array
     */
    protected function parse(Reflector $ref): array {
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
                throw new BusinessException($this->getRefName($ref) . " 注解 $tag 语法错误");
            }
        }
        return $array;
    }

    /**
     * 获取反射名称
     * @param Reflector $ref
     * @return string
     */
    protected function getRefName(Reflector $ref): string {
        if ($ref instanceof ReflectionClass) {
            return $ref->getName();
        } elseif ($ref instanceof ReflectionMethod) {
            return $ref->getDeclaringClass()->getName() . '::' . $ref->getName();
        } else {
            return '';
        }
    }

}
