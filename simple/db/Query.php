<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2018/9/4
 * Time: 上午11:02
 */

namespace simple\db;

use simple\Unit;

class Query
{
    public $sql = '';              //最后一次操作的sql语句
    public $table = '';            //表名
    public $db;                    //pdo连接对象

    /**
     * Query constructor.
     * @param string $database 数据库配置名
     * @param string $table 表名
     * @param string $otherDB 若用其他库
     * @throws \simple\exception\ApiException
     */
    public function __construct(string $database, string $table, string $otherDB = '')
    {
        $this->table = config('database.' . $database . '.prefix') . Unit::humpToLine($table,'_');
        !empty($otherDB) && $GLOBALS['config']['database'][$database]['database'] = $otherDB;
        $this->db = DB::getDb($database);
    }

    /**
     * 获取最后一次操作的sql语句
     * @return string
     */
    public function getLastSql(): string
    {
        return $this->sql;
    }

    /**
     * 数据获取方法
     * @param array $condition
     * @return array
     * @throws \Exception
     */
    public function select($condition = []): array
    {
        $set = Sql::select($this->table, $condition);

        $this->sql = $set['outSql'];

        $condition['_debug'] && debug($set['outSql']);

        $query = DB::query($this->db, $set['doSql'], $set['sqlParam']);

        return $query ? DB::getFetch($query) : [];
    }

    /**
     * 数据总行数获取
     * @param array $condition 筛选条件，数字为limit取几条，数组为筛选条件
     * @return int
     * @throws \Exception
     */
    public function getTotal($condition = []): int
    {
        foreach ($condition as $key=>$value){
            if (strpos($key,'_') === 0) unset($condition[$key]);
        }
        $condition['_select'] = ['count(1) as total'];
        return $this->select($condition)[0]['total'];
    }

    /**
     * 数据插入方法
     * @param array $data 数据数组
     * @return int 插入后的行id
     * @throws \Exception
     */
    public function insert(array $data): int
    {

        $set = Sql::insert($this->table, $data);

        $this->sql = $set['outSql'];

        $query = DB::query($this->db, $set['doSql'], $set['sqlParam']);

        return $query ? $this->db->lastInsertId() : -1;

    }

    /**
     * 数据更新修改方法
     * @param array $condition 行筛选条件
     * @param array $data 数据数组
     * @return int 影响行数
     * @throws \Exception
     */
    public function update(array $condition, array $data): int
    {

        $set = Sql::update($this->table, $condition, $data);

        $this->sql = $set['outSql'];

        $query = DB::query($this->db, $set['doSql'], $set['sqlParam']);

        return $query ? $query->rowCount() : -1;

    }

    /**
     * 数据删除
     * @param array $condition 行筛选条件
     * @return int 影响行数
     * @throws \Exception
     */
    public function delete(array $condition): int
    {
        $set = Sql::delete($this->table, $condition);

        $this->sql = $set['outSql'];

        $query = DB::query($this->db, $set['doSql'], $set['sqlParam']);

        return $query ? $query->rowCount() : -1;
    }

    /**
     * sql执行方法
     * @param string $sql 语句
     * @return array|int
     * @throws \simple\exception\ApiException
     */
    public function query(string $sql)
    {
        $query = DB::query($this->db, $sql);

        if (strpos(strtoupper($sql), 'SELECT') !== false) {
            return $query ? DB::getFetch($query) : [];
        } else {
            return $query ? $query->rowCount() : -1;
        }
    }

    /**
     * 开始事务
     */
    public function begin()
    {
        $this->db->beginTransaction();
    }

    /**
     * 提交事务
     */
    public function commit(){
        $this->db->commit();
    }

}