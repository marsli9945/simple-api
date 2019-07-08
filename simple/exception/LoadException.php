<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2019/1/29
 * Time: 5:42 PM
 */

namespace simple\exception;

use simple\Response;

class LoadException
{
    /**
     * 加载错误处理
     */
    public static function action()
    {
        self::closeSystemException();
        set_exception_handler([__CLASS__, 'exceptionHandler']);
    }

    /**
     * 关闭系统报错，改用自己的方式
     */
    public static function closeSystemException(): void
    {
        ini_set('display_startup_errors', 'Off');
        ini_set('display_errors', 'Off');
        ini_set('error_reporting', 'E_ALL & ~E_NOTICE');
        ini_set('log_errors', 'On');
    }

    /**
     * 异常处理方法
     * @param $exception
     * @throws \Exception
     */
    public static function exceptionHandler($exception){
        $httpCode = $exception->httpCode ?: 500;
        $trance[] = $exception->getFile() . ' in ' . $exception->getLine();
        foreach ($exception->getTrace() as $value) {
            $content = $value['file'] ?: $value['function'];
            $position = $value['line'] ?: $value['class'];
            $trance[] = $content . ' in ' . $position;
        }

        logger()->addError($exception->getMessage(),$trance);

        if (!isCli()){

            //报出错误信息，或只显示统一提升信息
            if (config('show_error_msg'))
                Response::apiResult($exception->getCode(), config('error_message'), '', $httpCode);
            else
                Response::apiResult($exception->getCode(), $exception->getMessage(), $trance, $httpCode);

        }else{

            debug([
                'status' => $exception->getCode(),
                'msg'    => $exception->getMessage(),
                'data'   => $trance
            ]);

        }
    }

}