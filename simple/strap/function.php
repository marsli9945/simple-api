<?php

/**
 * 当前运行环境是否为cli
 * @return bool
 */
function isCli()
{
    return php_sapi_name() == 'cli';
}

/**
 * 获取全局信息
 * @param string $name
 * @return mixed
 */
function getName(string $name = 'controller')
{

    $global = $GLOBALS;

    if (!empty($name)) {
        $arr = explode('.', $name);

        foreach ($arr as $v) {
            $v && $global = $global[$v] ?: '';
        }
    }

    return $global;
}

/**
 * 获取日志实例
 * @param string $name
 * @return \Monolog\Logger
 */
/**
 * @param string $name
 * @return \Monolog\Logger
 * @throws Exception
 */
function logger(string $name = 'default'): \Monolog\Logger
{
    if (!getName('logger.' . $name)) {

        //根据运行环境确定日志最终存放位置
        $project_path = config('is_phar') ? BUILD_PATH : PROJECT_PATH;
        $log_path = config('log_memory_path') ?: $project_path . '/log/';

        $logger = new \Monolog\Logger($name);
        $stream = new \Monolog\Handler\StreamHandler($log_path . 'simple.log', \Monolog\Logger::DEBUG);
        $stream->setFormatter(new \Monolog\Formatter\JsonFormatter());
        $logger->pushHandler($stream);
        $name == 'debug' && $logger->pushHandler(new \Monolog\Handler\BrowserConsoleHandler());
        $GLOBALS['logger'][$name] = $logger;
    }

    return getName('logger.' . $name);
}

/**
 * 获取配置信息
 * @param string|null $name 配置名以点间隔
 * @return array|string 配置信息数组
 */
function config(string $name = '')
{
    return getName('config.' . $name);
}

/**
 * 模型类助手函数
 * @param string $model 模型类类名
 * @param string|null $group 分组名，默认当前分组
 * @return \simple\db\Model 模型类实例
 */
function model(string $model, string $group = null): \simple\db\Model
{
    $group = empty($group) ? $GLOBALS['group'] : $group;
    $result = null;
    if (!empty($model)) {
        $modelName = 'app\\http\\' . $group . '\\model\\' . ucfirst($model);
        $result = !empty($GLOBALS['model'][$group][$model]) ? $GLOBALS['model'][$group][$model] : new $modelName();
        $GLOBALS['model'][$group][$model] = $result;
    }

    return $result;
}

/**
 * 数据快速操作类助手函数
 * @param string $database db配置名
 * @param string $table 表名
 * @param string $otherDB 连接配置下其他库名
 * @return \simple\db\Query 数据类实例
 * @throws \simple\exception\ApiException
 */
function query(string $database, string $table, string $otherDB = ''): \simple\db\Query
{
    $result = null;
    if ($database && $table) {
        if (empty($GLOBALS['query'][$database][$otherDB]))
            $GLOBALS['query'][$database][$otherDB] = new \simple\db\Query($database, $table, $otherDB);

        $result = $GLOBALS['query'][$database][$otherDB];
        $result->table = config("database." . $database . ".prefix") . \simple\Unit::humpToLine($table, '_');
    }
    return $result;
}

/**
 * 信息打印方法
 * @param mixed ...$args
 */
function debug(...$args)
{
    //显示方法调用位置
    if (config('debug_show_line')) {
        $position = current(debug_backtrace());
        echo "{$position['file']}  {$position['line']}" . PHP_EOL;
    }

    //在命令行模式,用手动换行的方式
    if (isCli()) {
        foreach ($args as $value) {
            print_r($value);
            echo PHP_EOL;
        }
    } else {
        foreach ($args as $value) {
            echo "<pre>";
            print_r($value);
            echo "</pre>";
        }
    }
}

/**
 * 获取request请求传递的参数
 * @param string $key http请求类型
 * @param array $default 默认需要的数据
 * @return array 数据
 */
function input(string $key = '', array $default = []): array
{
    return \simple\Unit::arrayMerge(\simple\Request::input($key), $default);
}

/**
 * api成功回调方法
 * @param array $data 回调数据
 * @throws Exception
 */
function success($data = []): void
{
    config('result_success_log') && logger()->info('success',$data);
    \simple\Response::apiResult(1, 'success', $data, 200);
}

/**
 * api错误回调方法
 * @param string $msg 提示信息
 * @throws Exception
 */
function error(string $msg = 'error'): void
{
    config('result_error_log') && logger()->notice($msg);
    \simple\Response::apiResult(2, $msg, '', 200);
}

/**
 * 多级分组键值索引
 * @param array $array
 * @return array
 */
function ArrayReGroup($array)
{
    $argList = func_get_args();
    $argNum = func_num_args();

    if (!$array)
        return $array;

    if (!isset($argList[1]))
        return $array;

    if ((strpos($argList[1], ',') !== false || strpos($argList[1], '.') !== false) && (count($argList) == 2 || (count($argList) == 3 && $argList[2] === true))) {
        $path = preg_replace('/\s/is', '', $argList[1]);
        $newArgList = array_merge([$array], preg_split('/[.,]/is', $path));

        if ($argList[2])
            $newArgList[] = $argList[2];

        $argList = $newArgList;
        $argNum = count($argList);
    }

    $new = [];

    foreach ($array as $val) {
        $tmp = &$new;
        for ($i = 1; $i < $argNum; $i++) {
            if ($argList[$i] === true)
                break;

            $n = $val[trim($argList[$i])];

            if (!$tmp[$n])
                $tmp[$n] = [];

            $tmp = &$tmp[$n];
        }

        // 最后一个参数是true的情况下,强制索引最后一个
        if ($argList[$i] === true)
            $tmp = $val;
        else
            $tmp[] = $val;
    }

    return $new;
}
