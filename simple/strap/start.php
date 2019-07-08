<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2019/1/28
 * Time: 上午10:16
 */

//定义项目跟目录
define('PROJECT_PATH', __DIR__ . '/../..');

// 定义应用目录
define('APP_PATH', __DIR__ . '/../../app');

//主配置文件目录
define('CONF_PATH', __DIR__ . '/../../config');

//自动加载类
require_once __DIR__ . '/../../vendor/autoload.php';

//代码包所在目录
define('BUILD_PATH', \simple\Unit::getBuildPath(__DIR__));

//加载系统助手函数
require_once __DIR__ . '/function.php';

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|   通过run方法加载
|   所有必须的类
|   框架配置和错误处理
*/
\simple\App::run();
