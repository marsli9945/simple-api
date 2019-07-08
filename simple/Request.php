<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2019/1/29
 * Time: 5:56 PM
 */

namespace simple;


class Request
{
    /**
     * 从资源流中获取参数
     * @return array
     */
    public static function getInput(): array
    {
        return file_get_contents("php://input") ? json_decode(file_get_contents("php://input"), true) : [];
    }

    /**
     * post参数获取方法
     * @return array
     */
    public static function getPost(): array
    {

        if (strtoupper($_SERVER['CONTENT_TYPE']) == 'APPLICATION/JSON;CHARSET=UTF-8')
            return self::getInput();

        return $_POST;

    }

    /**
     * delete参数获取方法
     * @return array
     */
    public static function getDelete():array
    {

        if (strtoupper($_SERVER['CONTENT_TYPE']) == 'APPLICATION/JSON;CHARSET=UTF-8')
            return self::getInput();

        return $_GET;
    }

    /**
     * 获取request请求传递的参数
     * @param string $key http请求类型
     * @return array 数据
     */
    public static function input(string $key = ''): array
    {
        switch ($key) {
            case 'get':
                $request = $_GET;
                break;
            case 'post':
                $request = self::getPost();
                break;
            case 'put':
                $request = self::getInput();
                break;
            case 'delete':
                $request = self::getDelete();
                break;
            default:
                $request = array_merge($_GET, self::getPost());
                break;
        }

        return Unit::recuArr($request);
    }

}