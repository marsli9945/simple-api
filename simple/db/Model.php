<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2018/6/12
 * Time: 下午3:20
 */

namespace simple\db;

use simple\Unit;

class Model
{

    public $database;               //数据库配置名
    public $otherDB = '';           //若需当前连接该地址下其他库，填写库名
    public $table = '';             //表名
    public $db = '';                //DB操作对象
    public $query = '';             //sql操作对象
    public $field = '';             //查询字段，默认所有字段
    public $dictionaries;           //字段数据字典
    public $primary;                //主键

    /**
     * Model constructor.
     * @throws \simple\exception\ApiException
     */
    public function __construct()
    {
        $database = getName('group');
        $database = config('model_default_database') ?: $database;
        $database = $this->database ?: $database;
        $path_arr = explode('\\', get_called_class());
        $table = $this->table ?: Unit::humpToLine(end($path_arr), '_');
        $this->query = query($database, $table, $this->otherDB);
        $this->primary = $this->primary ?: config('model_default_primary');
    }

    /**
     * 设置字段的字典映射
     * @param string $field 字段
     * @param array $dic 映射数组
     */
    public function setDic($field = '', $dic = [])
    {
        if (!empty($field) && !empty($dic))
            $this->dictionaries[$field] = $dic;
    }

    /**
     * 将结果集数据中有数据字典到字段
     * 进行字典映射
     * @param array $list 结果集合
     * @return array 转化后到结果数组
     */
    private function saveDic(array $list = []): array
    {

        if (empty($list)) return $list;

        foreach ($list as &$value) {
            foreach ($value as $k => &$v) {
                if ($this->dictionaries[$k]) {
                    $v = $this->dictionaries[$k][$v] ?: $v;
                }
            }
        }

        return $list;
    }

    /**
     * 获取最后一次操作的sql语句
     * @return string
     */
    public function getLastSql(): string
    {
        return $this->query->getLastSql();
    }

    /**
     * 数据获取方法
     * @param array $condition 筛选条件，数字为limit取几条，数组为筛选条件
     * @return array|null
     * @throws \Exception
     */
    public function select($condition = []): array
    {
        $this->field && $condition['_select'] = $this->field;
        return $this->saveDic($this->query->select($condition));
    }

    /**
     * 按主键查询
     * @param $primary
     * @return array
     * @throws \Exception
     */
    public function selectByPrimary($primary)
    {
        $condition[$this->primary] = $primary;
        $this->field && $condition['_select'] = $this->field;
        return $this->saveDic($this->query->select($condition));
    }

    /**
     * 数据总行数获取
     * @param array $condition 筛选条件，数字为limit取几条，数组为筛选条件
     * @return int
     * @throws \Exception
     */
    public function getTotal($condition = []): int
    {
        return $this->query->getTotal($condition);
    }

    /**
     * 数据插入方法
     * @param array $data 数据数组
     * @return int 插入后的行id
     * @throws \Exception
     */
    public function insert(array $data): int
    {
        return $this->query->insert($data);
    }

    /**
     * 按ID更新数据
     * @param $id
     * @param array $data 数据集
     * @return int
     * @throws \Exception
     */
    public function updateById(int $id, array $data): int
    {
        return $this->query->update(['id' => $id], $data);
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
        return $this->query->update($condition, $data);
    }

    /**
     * 按ID删除数据
     * @param int $id
     * @return int
     * @throws \Exception
     */
    public function deleteById(int $id): int
    {
        return $this->query->delete(['id' => $id]);
    }

    /**
     * 按条件数据删除
     * @param array $condition 行筛选条件
     * @return int 影响行数
     * @throws \Exception
     */
    public function delete(array $condition): int
    {
        return $this->query->delete($condition);
    }

    /**
     * sql执行方法
     * @param string $sql 语句
     * @return array|int
     * @throws \simple\exception\ApiException
     */
    public function query(string $sql)
    {
        return $this->query->query($sql);
    }

    /**
     * 开始事务
     */
    public function begin()
    {
        $this->query->begin();
    }

    /**
     * 提交事务
     */
    public function commit()
    {
        $this->query->commit();
    }


}