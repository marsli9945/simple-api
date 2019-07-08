<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2019/2/28
 * Time: 6:21 PM
 */

namespace app\common\unit;


class PassUnit
{
    /**
     * 对密码加密
     * @param $password
     * @return string
     */
    public static function setPassword($password): string
    {
        return md5($password);
    }

    /**
     * 加密生成token
     * @param $user_name
     * @param $password
     * @return string
     */
    public static function setToken($user_name, $password): string
    {
        return md5($user_name . $password . date());
    }

}