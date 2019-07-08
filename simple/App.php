<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2019/1/29
 * Time: 10:34 AM
 */

namespace simple;


use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\WebProcessor;
use simple\controller\Controller;
use simple\controller\QueryController;
use simple\controller\Restful;
use simple\exception\ApiException;
use simple\exception\LoadException;

class App
{
    /**
     * 框架加载启动方法
     * @throws ApiException
     */
    public static function run(): void
    {
        //加载全局配置文件
        self::mountConf();

        //加载外部全局配置文件
        self::mountConf(BUILD_PATH . '/config');

        //加载log
        self::mountLog();

        //加载错误处理
        LoadException::action();

        //加载http头信息
        self::mountHeader();

        //web启动需要处理路由
        if (!isCli()) {
            self::mountRoute();
        }
    }

    /**
     * 加载目录下所有配置
     * 放在全局变量中
     * @param string $path 默认为系统配置目录
     */
    public static function mountConf(string $path = CONF_PATH): void
    {
        global $config;
        $config_default = [];
        $config_extend = [];
        $pathArr = Unit::requireDir($path);
        foreach ($pathArr as $name => $file) {
            if (Unit::getFileExtension($file) !== 'ini') continue;

            if ($name == 'config') {
                $config_default = parse_ini_file($file, true);
            } else {
                $config_extend[$name] = parse_ini_file($file, true);
            }
        }

        if ($path == CONF_PATH)
            $config = Unit::arrayMerge($config_default, $config_extend);
        else
            $config = Unit::arrayMerge($config, Unit::arrayMerge($config_default, $config_extend));
    }

    /**
     * 日志功能类加载
     * @throws \Exception
     */
    public static function mountLog()
    {
        global $logger;

        //根据运行环境确定日志最终存放位置
        $project_path = config('is_phar') ? BUILD_PATH : PROJECT_PATH;
        $log_path = config('log_memory_path') ?: $project_path . '/log';
        $log_path .= isCli() ? '/cli/' : '/http/';

        //访问日志
        $logger['default'] = new Logger('HTTP');
        //http所有日志
        $http_debug = new RotatingFileHandler($log_path . 'daily.log',0,Logger::INFO);
        $http_debug->setFormatter(new JsonFormatter());
        $logger['default']->pushHandler($http_debug);
        //http错误日志
        $http_error = new StreamHandler($log_path . 'error.log', Logger::WARNING);
        $http_error->setFormatter(new JsonFormatter());
        $logger['default']->pushHandler($http_error);
        //浏览器打印
        config('default_log_debug') && $logger['default']->pushHandler(new BrowserConsoleHandler());

        //根据运行环境对不同，额外添加http访问信息或脚本文件信息
        $base_processor = isCli() ? new IntrospectionProcessor() : new WebProcessor();
        $logger['default']->pushProcessor($base_processor);

        $logger['default']->pushProcessor(new MemoryPeakUsageProcessor()); //额外添加内存信息

    }

    /**
     * 解析路由
     * @throws ApiException
     * @throws \Exception
     */
    public static function mountRoute(): void
    {
        global $group;
        global $controller;
        global $action;

        $route = $_SERVER['PATH_INFO'];

        //浏览器对.ico的请求单独处理
        if ($route == '/favicon.ico') {

            require_once PROJECT_PATH . '/favicon.ico';

        } else {

            //路由配置项
            $route = config("route.{$route}") ? config("route.{$route}.path") : $route;

            //拆分路由
            $param = explode('/', $route);

            //最终寻址路径大于四层，无法正常解析
            if (count($param) > 4)
                throw new ApiException('非法路由', 401);

            $group = $param[1] ?: 'index';
            $controller = $param[2] ?: 'index';
            $action = $param[3] ?: 'index';
            $id = $action;

            if (count($param) == 3)
                $action = Restful::RESTFUL_METHOD[$_SERVER['REQUEST_METHOD']];

            count($param) == 4 && is_numeric($action) && $action = 'getInfo';

            $_SERVER['REQUEST_METHOD'] == 'OPTIONS' && $action = 'options';

            $controllerName = 'app\\http\\' . $group . '\\controller\\' . ucfirst($controller);

            //控制器存在正常加载，不存在加载虚拟控制器QueryController
            if (!class_exists($controllerName)) {
                if (config('database.' . $group)) {
                    $controllerClass = new QueryController();
                } else {
                    throw new ApiException('控制器不存在', 404);
                }
            } else {
                $controllerClass = new $controllerName();
            }

            //加载本分组模块的配置
            self::mountConf(CONF_PATH . '/' . $group);      //主配置目录下的组配置
            self::mountConf(APP_PATH . '/http/' . $group);       //分组目录下的配置
            self::mountConf(BUILD_PATH . '/config/' . $group);     //外部配置中的组配置目录

            if (!$controllerClass instanceof Controller)
                throw new ApiException('非法控制器', 402);

            if (!method_exists($controllerClass, $action))
                throw new ApiException('访问的方法不存在', 404);

            if ($_SERVER['REQUEST_METHOD'] != 'OPTIONS')
                logger()->info('API Success');

            $controllerClass->$action($id);

        }

    }

    /**
     * 设置http头信息，解决前端调用api跨域问题
     */
    public static function mountHeader()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Method: *');
        header('Access-Control-Allow-Headers: *');
    }

}
