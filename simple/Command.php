<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2019/2/26
 * Time: 4:29 PM
 */

namespace simple;


use simple\exception\LoadException;

class Command
{
    public static function run()
    {
        if (getName('argc') > 1) {
            call_user_func([__CLASS__, getName('argv')[1]], getName('argv'), getName('argc'));
        }
    }

    /**
     * 打包
     * @param array $argv
     * @throws \Exception
     */
    public static function build(array $argv): void
    {
        $build_path = $argv[2] ? "build/$argv[2].phar" : 'build/simple.phar';

        try {

            file_exists($build_path) && \Phar::unlinkArchive($build_path);

            $phar = new \Phar($build_path, 0, 'simple.phar');

            $phar->startBuffering();

            $phar->buildFromDirectory(PROJECT_PATH);

            foreach (config('package_exclude_file') as $file) {
                $phar->delete($file);
            }

            foreach (config('package_exclude_dir') as $dir) {
                Unit::recursionDir($dir, function ($file) use ($phar) {
                    $phar->delete($file);
                });
            }

            $phar->setDefaultStub('simple/strap/start.php', 'simple/strap/webStart.php');

            $phar->compressFiles(\Phar::GZ);

            $phar->stopBuffering();

        } catch (\Exception $exception) {

            LoadException::exceptionHandler($exception);
            exit();

        }

        debug('simple: build success');
    }

    /**
     * 业务类生成器
     * @param array $argv
     * @param int $argc
     */
    public static function make(array $argv, int $argc): void
    {
        if ($argc < 4) exit('simple: command error' . PHP_EOL);

        $group = APP_PATH . "/{$argv[2]}";
        $name = ucfirst($argv[3]);
        $controller = "/{$group}/controller/{$name}.php";
        $model = "{$group}/model/{$name}.php";

        !is_dir($group) && mkdir($group);
        !is_dir($group . "/controller") && mkdir($group . "/controller");

        file_exists($controller) && debug('controller exists!');

        if (in_array('--no_model', $argv)) {
            $controller_content = <<<eof
<?php

namespace app\\$argv[2]\controller;


use simpleBoot\controller\QueryController;

class {$name} extends QueryController
{


}
eof;
            $fp = fopen($controller, 'w');
            if (!fwrite($fp, $controller_content)) {
                debug('controller write field');
            }
            fclose($fp);

            exec("chmod 777 " . $controller);

        } else {
            //创建model文件夹
            !is_dir($group . "/model") && mkdir($group . "/model");

            //写入控制器
            $controller_content = <<<eof
<?php

namespace app\\$argv[2]\controller;


use simpleBoot\controller\ModelController;

class {$name} extends ModelController
{

}
eof;

            $fp = fopen($controller, 'w');
            if (!fwrite($fp, $controller_content)) {
                debug('controller write field');
            }
            fclose($fp);

            exec("chmod 777 " . $controller);

            //写入模型类
            file_exists($model) && debug('model exists!');
            $model_content = <<<eof
<?php

namespace app\\$argv[2]\model;


use simpleBoot\db\Model;

class {$name} extends Model
{

}
eof;

            $fp = fopen($model, 'w');
            if (!fwrite($fp, $model_content)) {
                debug('model write field');
            }
            fclose($fp);

            exec("chmod 777 " . $controller);

        }
    }

    public static function __callStatic($name, $arguments)
    {
        debug("simple: invalid command: '$name'");
    }

}