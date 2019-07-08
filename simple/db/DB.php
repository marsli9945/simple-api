<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2018/6/12
 * Time: 下午3:58
 */

namespace simple\db;


use simple\exception\ApiException;
use simple\Unit;

class DB
{

    /**
     * @param string $database
     * @return \PDO
     * @throws ApiException
     */
    public static function getDb(string $database): \PDO
    {
        if (!is_array(config('database.' . $database)))
            throw new ApiException('请确保db配置有效');

        global $db;

        if (!$db[$database])
            $db[$database] = self::connet(config('database.' . $database));

        return $db[$database];
    }

    /**
     * 连接数据库
     * @param array $database database配置数组
     * @return \PDO 连接的pdo对象
     * @throws ApiException
     */
    public static function connet(array $database): \PDO
    {
        $dsn = $database['dbms'] . ":host=" . $database['host'] . ";dbname=" . $database['database'];
        try {
            $dbh = new \PDO($dsn, $database['user'], $database['password']);                   //初始化一个PDO对象
            $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);      //设置抛出异常模式处理错误
            $dbh->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);             //支持预处理
            $dbh->setAttribute(\PDO::ATTR_PERSISTENT, true);
            $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return $dbh;
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * sql执行方法
     * @param \PDO $db
     * @param string $sql
     * @param array $param
     * @return \PDOStatement
     * @throws ApiException
     */
    public static function query(\PDO $db, string $sql, array $param = []): \PDOStatement
    {
        if (empty($param)) {
            try {
                $query = $db->query($sql);
            } catch (\Exception $exception) {
                throw new ApiException($exception->getMessage() . ' of ' . $sql);
            }

        } else {
            try {
                $query = $db->prepare($sql);
                $query->execute($param);
            } catch (\Exception $exception) {
                throw new ApiException($exception->getMessage() . ' of ' . $sql);
            }
        }
        return $query;
    }

    /**
     * 处理select查询语句
     * 产生的结果集
     * @param \PDOStatement $query sql执行结果句柄
     * @return array 结果数组
     */
    public static function getFetch(\PDOStatement $query): array
    {
        $list = [];
        while ($row = $query->fetch(\PDO::FETCH_OBJ)) {
            array_push($list, Unit::objToArray($row));
        }

        return $list;
    }

}