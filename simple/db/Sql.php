<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2019/2/1
 * Time: 10:34 AM
 */

namespace simple\db;

class Sql
{
    /**
     * 数据查询
     * @param string $table 表名
     * @param array/int $condition 检索条件
     * @return array
     */
    public static function select(string $table, $condition = []): array
    {
        $set = [
            'outSql' => '',
            'doSql' => '',
            'sqlParam' => []
        ];

        $select = $condition['_select'] ? implode(',', $condition['_select']) : '*';

        $sql = 'SELECT ' . $select . ' FROM ' . $table;

        if (empty($condition)){
            $set['doSql'] = $set['outSql'] = $sql;
        }

        if (is_array($condition) && !empty($condition)) {

            $group_str = $condition['_group'] ? ' GROUP BY ' . implode(',', $condition['_group']) : '';

            $order_str = $condition['_order'] ? self::splitOrder($condition['_order']) : '';

            $limit_str = $condition['_limit'] ? self::splitLimit($condition['_limit']) : '';

            $where = self::splitCondition($condition);

            $set['sqlParam'] = $where['param'];

//            debug($where);die();

            $set['outSql'] = $sql . ' WHERE 1=1 ' . $where['outSql'] . $group_str . $order_str . $limit_str;
            $set['doSql'] = $sql . ' WHERE 1=1 ' . $where['doSql'] . $group_str . $order_str . $limit_str;

        } elseif (is_int($condition)) {

            $set['doSql'] = $set['outSql'] = $sql . ' LIMIT ' . $condition;

        }

        return $set;
    }

    /**
     * 数据插入方法
     * @param string $table 表名
     * @param array $data 数据列
     * @return array
     */
    public static function insert(string $table, array $data): array
    {

        if (count($data) < 1) return null;

        $param = [];
        $front = [];
        $after = [];
        $out_after = [];
        $sql = "INSERT INTO " . $table;
        foreach ($data as $k => $v) {
            $param[':' . $k] = $v;
            array_push($front, $k);
            array_push($after, ':' . $k);
            array_push($out_after, $v);
        }

        $front_str = "(" . implode(",", $front) . ")";
        $after_str = "(" . implode(",", $after) . ")";
        $out_after_str = "('" . implode("','", $out_after) . "')";

        return [
            'outSql' => $sql . ' ' . $front_str . ' VALUES ' . $out_after_str,
            'doSql' => $sql . ' ' . $front_str . ' VALUES ' . $after_str,
            'sqlParam' => $param
        ];
    }

    /**
     * 数据更新修改方法
     * @param string $table 表名
     * @param array $condition 行筛选条件
     * @param array $data 数据数组
     * @return array
     */
    public static function update(string $table, array $condition, array $data): array
    {

        if (count($condition) < 1 || count($data) < 1) return [];

        $sql = "UPDATE " . $table . ' SET';

        $where = self::splitCondition($condition);
        $set = self::splitSet($data);

        $param = array_merge($where['param'], $set['param']);

        return [
            'outSql' => $sql . ' ' . $set['outSql'] . ' WHERE 1=1 ' . $where['outSql'],
            'doSql' => $sql . ' ' . $set['doSql'] . ' WHERE 1=1 ' . $where['doSql'],
            'sqlParam' => $param
        ];
    }

    /**
     * 数据删除
     * @param string $table 表名
     * @param array $condition 条件
     * @return array
     */
    public static function delete(string $table, array $condition): array
    {
        if (count($condition) < 1) return null;

        $sql = 'DELETE FROM ' . $table . ' WHERE 1=1 ';

        $where = self::splitCondition($condition);

        return [
            'outSql' => $sql . $where['outSql'],
            'doSql' => $sql . $where['doSql'],
            'sqlParam' => $where['param']

        ];
    }

    /**
     * 拆分条件参数方法
     * @param array $condition 条件数组
     * @param string $field 二级差分时需要传递条件字段
     * @return array 分割后的数组或字符串
     */
    public static function splitCondition(array $condition, string $field = ''): array
    {
        $set = [
            'outSql' => [],
            'doSql' => [],
            'param' => []
        ];

        foreach ($condition as $k => $v) {
            if (substr($k, 0, 1) == '_') continue;
            if (empty($v) && $v !== 0) continue;

            if (is_array($v) && $k != 'in') {
                $sub_set = self::splitCondition($v, $k);
                $set['outSql'] = array_merge($set['outSql'], $sub_set['outSql']);
                $set['doSql'] = array_merge($set['doSql'], $sub_set['doSql']);
                $set['param'] = array_merge($set['param'], $sub_set['param']);
                continue;
            }

            static $i = 0;//条件计数器，解决同一字段多条件筛选问题

            if (empty($field)) {
                $set['param'][':' . $i . $k] = $v;
                $set['doSql'][] = "`{$k}` = :{$i}{$k}";
                $set['outSql'][] = "`{$k}` = '{$v}'";
                $i++;
            } else {
                switch ($k) {
                    case 'like':
                        $set['doSql'][] = "`{$field}` like '%{$v}%'";
                        $set['outSql'][] = "`{$field}` like '%{$v}%'";
                        break;
                    case 'in':
                        $set['doSql'][] = "`{$field}` in (" . implode(',', $v) . ")";
                        $set['outSql'][] = "`{$field}` in (" . implode(',', $v) . ")";
                        break;
                    default:
                        $set['param'][':' . $i . $field] = $v;
                        $set['doSql'][] = "`{$field}` {$k} :{$i}{$field}";
                        $set['outSql'][] = "`{$field}` {$k} '{$v}'";
                        $i++;
                }
            }

        }

        if (!$field) {
            $set['doSql'] = $set['doSql'] ? ' AND ' . implode(' AND ', $set['doSql']) : '';
            $set['outSql'] = $set['outSql'] ? ' AND ' . implode(' AND ', $set['outSql']) : '';
        }

        return $set;
    }

    /**
     * update语句中
     * set条件处理方法
     * @param array $set 条件
     * @return array
     */
    public static function splitSet(array $set): array
    {
        $param = [];
        $arr = [];
        $out_arr = [];
        foreach ($set as $k => $v) {
            $param[':s_' . $k] = $v;
            array_push($arr, "`$k`" . '=:s_' . $k);
            array_push($out_arr, "`$k`" . "='" . $v . "'");
        }

        $result = [];
        $result['doSql'] = implode(',', $arr);
        $result['outSql'] = implode(',', $out_arr);

        $result['param'] = $param;
        return $result;
    }

    /**
     * order条件处理方法
     * @param array $order
     * return string
     * @return string
     */
    public static function splitOrder(array $order): string
    {
        $str = ' ORDER BY ';
        foreach ($order as $key => $value) {
            if (is_int($key)) {//只传入排序字段，默认升序
                $str .= $value . ' ASC ';
            } elseif (is_string($key)) {//指明字段排序方式
                $desc = $value ? 'DESC' : 'ASC';
                $str .= $key . ' ' . $desc . ' ';
            }
        }
        return $str;
    }

    /**
     * limit条件处理方法
     * @param array $limit
     * @return string
     */
    public static function splitLimit($limit): string
    {
        $str = ' LIMIT ';

        if (!is_array($limit)) {
            return $str . intval($limit);
        } else {
            if (count($limit) == 2) {
                return $str . implode(',', $limit);
            }
        }
        return '';
    }

}