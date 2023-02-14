<?php

/*
 * 调试转发
 */

function respondSuccess(array $data = []) {
    die(json_encode(['data' => $data, 'code' => 'ok'], JSON_UNESCAPED_UNICODE));
}

function respondFail($msg) {
    die(json_encode(['msg' => $msg, 'code' => 'no'], JSON_UNESCAPED_UNICODE));
}

// 参数验证
function checkParam(string $key, array $types = null, array $options = null) {
    if (!isset($_POST[$key])) {
        respondFail("{$key} 参数不存在");
    }
    if ($types && !in_array(gettype($_POST[$key]), $types, true)) {
        respondFail("{$key} 参数必需是 " . implode('、', $types) . " 类型");
    }
    if (is_array($options) && !in_array($_POST[$key], $options)) {
        respondFail("{$key} 参数可选值：" . implode('、', $options));
    }
}

// 验证可选参数
function checkOptionalParam(string $key, array $types, $default = null) {
    if (!isset($_POST[$key])) {
        $_POST[$key] = $default;
    }
    checkParam($key, $types);
}

// 整理上传文件
function restructureFiles(array $files) {
    foreach ($files as &$file) {
        $_array = [];
        foreach ($file as $prop => $propval) {
            if (is_array($propval)) {
                array_walk_recursive($propval, function(&$item, $key) use($prop) {
                    $item = array($prop => $item);
                }, $file);
                $_array = array_replace_recursive($_array, $propval);
            } else {
                $_array[$prop] = $propval;
            }
        }
        $file = $_array;
    }
    return $files['files'] ?? [];
}

// 生成上传文件
function makeFiles(array $files, array &$params, string $before = '') {
    foreach ($files as $key => $file) {
        $index = $before ? $before . "[{$key}]" : $key;
        if (is_array(current($file))) {
            if (empty($params[$key]) || !is_array($params[$key])) {
                $params[$key] = [];
            }
            makeFiles($file, $params[$key], $index);
        } else {
            if ($file['error'] === UPLOAD_ERR_OK) {
                $params[$key] = new CURLFile($file['tmp_name'], $file['type']);
                continue;
            }
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    respondFail("{$index} 上传文件超过PHP限制大小");
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    respondFail("{$index} 上传文件超过PHP限制个数");
                    break;
                case UPLOAD_ERR_PARTIAL:
                    respondFail("{$index} 文件只上传部分");
                    break;
                case UPLOAD_ERR_NO_FILE:
                    respondFail("{$index} 没有上传文件信息");
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    respondFail("{$index} 上传文件找不到临时文件夹");
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    respondFail("{$index} 上传文件写入失败");
                    break;
                case UPLOAD_ERR_EXTENSION:
                    respondFail("{$index} 上传文件扩展错误");
                    break;
                default:
                    respondFail("{$index} 上传文件错误");
                    break;
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respondFail('必需是POST请求');
}
// 基本参数
checkParam('url', ['string']);
checkParam('method', ['string'], ['GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS']);
// 功能参数
checkOptionalParam('params', ['array', 'string'], []);
checkOptionalParam('cookies', ['array'], []);
checkOptionalParam('headers', ['array'], []);
// 请求处理
$url = $_POST['url'];
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CUSTOMREQUEST => $_POST['method'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_SAFE_UPLOAD => true,
    CURLOPT_SSL_VERIFYPEER => false, // 不验证证书
    CURLOPT_SSL_VERIFYHOST => false, // 不验证域名
]);
// 传参数
$params = $_POST['params'];
if (in_array($_POST['method'], ['POST', 'PUT'], true)) {
    curl_setopt($curl, CURLOPT_POST, true);
    if (is_array($params)) {
        // 整理上传文件
        $uploadFiles = [];
        makeFiles(restructureFiles($_FILES), $params);
    }
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    curl_setopt($curl, CURLOPT_URL, $url);
} else {
    curl_setopt($curl, CURLOPT_URL, $url . (strpos($url, '?') ? '&' : '?') . (is_string($params) ? $params : http_build_query($params)));
}
// 传头信息
$headers = [];
foreach ($_POST['headers'] as $key => $value) {
    settype($value, 'string');
    $headers[] = "{$key}: {$value}";
}
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
// 传cookie信息
$cookies = [];
foreach ($_POST['cookies'] as $key => $value) {
    settype($value, 'string');
    $cookies[] = "{$key}: {$value}";
}
curl_setopt($curl, CURLOPT_COOKIE, implode('; ', $cookies));
// 发送请求
$response = curl_exec($curl);
$size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
curl_close($curl);
header('Access-Control-Allow-Origin: *');
respondSuccess([
    'body' => substr($response, $size),
    'header' => substr($response, 0, $size),
]);
