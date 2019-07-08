<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2018/12/7
 * Time: 2:53 PM
 */

namespace simple;

/**
 * 框架工具类
 * Class Unit
 * @package lib\exception
 */
class Unit
{
    /**
     * 递归获取目录下所有文件名及目录从属关系
     * 带回调可以对每一个文件进行处理
     * @param string $dir 目录路径
     * @param callable|null $callback 回调函数
     * @return array 包含所有文件名的数组
     */
    public static function recursionDir(string $dir, callable $callback = null): array
    {
        $file_arr = [];
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..') {
                        if (is_dir($dir . '/' . $file)) {
                            $file_arr[$file] = self::recursionDir($dir . '/' . $file, $callback);
                        } else {
                            list($name) = explode('.', $file);
                            $file_arr[$name] = $dir . '/' . $file;
                            if ($callback) $callback($file_arr[$name]);
                        }
                    }

                }
                closedir($dh);
            }
        }
        return $file_arr;
    }

    /**
     * 获取目录下所有文件名
     * @param string $dir 目录路径
     * @return array 包含所有文件名的数组
     */
    public static function requireDir(string $dir): array
    {
        $file_arr = [];
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..') {
                        list($name) = explode('.', $file);
                        $file_arr[$name] = $dir . '/' . $file;
                    }

                }
                closedir($dh);
            }
        }
        return $file_arr;
    }

    /**
     * 自定义递归合并数组
     * @param array $default 主数组
     * @param array $extend 需要插入合并的数组
     * @return array 合并后的数组
     */
    public static function arrayMerge(array $default, array $extend): array
    {
        foreach ($extend as $key => $value) {
            if (is_array($value) && array_key_exists($key, $default)) {
                $default[$key] = self::arrayMerge($default[$key], $value);
            } else {
                $default[$key] = $value;
            }
        }
        return $default;
    }

    /**
     * @param object $obj
     * @return array
     */
    public static function objToArray($obj): array
    {
        $obj = (array)$obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return [];
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array)object_to_array($v);
            }
        }

        return $obj;
    }


    /**
     * 驼峰字符串转自定义分隔符
     * @param $str
     * @param string $sep 分隔符(默认为空格)
     * @return bool|null|string|string[]
     */
    public static function humpToLine(string $str, string $sep = ' '): string
    {
        $str = preg_replace_callback('/([A-Z]{1})/', function ($matches) use ($sep) {
            return $sep . strtolower($matches[0]);
        }, $str);
        strpos($str, $sep) === 0 && $str = substr($str, 1);
        return $str;
    }

    /**
     * 分隔符字符串转驼峰
     * @param $str
     * @param string $sep 分隔符(可传多个 -*_ 连着写就行)
     * @return null|string|string[]
     */
    public static function lineToHump(string $str, string $sep = ' '): string
    {
        $str = preg_replace_callback("/([$sep]+([a-z]{1}))/i", function ($matches) {
            return strtoupper($matches[2]);
        }, $str);
        return $str;
    }

    /**
     * 递归处理接收数据
     * 将所有json字符串转为可操作数组
     * @param $arr
     * @return mixed
     */
    public static function recuArr(array $arr): array
    {
        foreach ($arr as &$value) {
            if (is_array($value))
                $value = self::recuArr($value);

            $str = json_decode($value, true);
            !is_null($str) && $value = $str;
        }

        return $arr;
    }

    /**
     * limit条件转化
     * [页数,条数]转为[起始位置,条数]
     * @param array $limit
     * @return array
     */
    public static function formatLimit(array $limit): array
    {
        if (!$limit) return [];
        return [($limit['page'] - 1) * $limit['size'], $limit['size']];
    }

    /**
     * 获取文件扩展名
     * @param string $file 文件全路径或文件全名
     * @return string 扩展名
     */
    public static function getFileExtension(string $file): string
    {
        preg_match_all('/\.([a-z]+)/', $file, $ext);
        return $ext[1] ? end($ext[1]) : '';
    }

    /**
     * 获取phar包所在目录
     * @param string $start_path start文件所在目录
     * @return string
     */
    public static function getBuildPath(string $start_path): string
    {
        $dir_arr = explode('/', $start_path);
        $dir_arr = array_slice($dir_arr, 3);
        $dir_arr = array_slice($dir_arr, 0, count($dir_arr) - 3);
        return '/' . implode('/', $dir_arr);
    }

}