<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2019/1/29
 * Time: 5:41 PM
 */

namespace simple\exception;

/**
 * 自定义接口报错类
 * Class ApiException
 * @package simple\exception
 */
class ApiException extends \Exception
{
    //提示信息
    public  $message;
    //http状态码
    public $httpCode;
    //系统状态码
    public $code;

    public function __construct($message = "", $httpCode = 500, $code = 3)
    {
        $this->message = $message;
        $this->httpCode = $httpCode;
        $this->code = $code;
    }
}